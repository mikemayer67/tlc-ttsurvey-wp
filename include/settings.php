<?php
namespace TLC\TTSurvey;

/**
 * Setup and handling of plugin settings
 *
 * The plugin settings are stored as a single json encoded dictionary in the WP options database.
 * It contains the following fields (which may or may not be present):
 *
 */

if( ! defined('WPINC') ) { die; }

const OPTIONS_KEY = 'tlc_ttsurvey_options';

const CAPS_KEY = 'caps';
const PDF_URI_KEY = 'pdf_href';
const LOG_LEVEL_KEY = 'log_level';

$option_defaults = array(
  CAPS_KEY => [],
  PDF_URI_KEY => '',
  LOG_LEVEL_KEY => 'INFO',
);

/**
 * get option value
 *
 * Returns null if the option isn't currently set and there's no default value
 *
 * @param string $key option key to retrieve
 * @return string or null
 */
function get_survey_option($key)
{
  $options = get_option(OPTIONS_KEY,array());
  return $options[$key] ?? ($option_defaults[$key] ?? null);
}

/**
 * set option value
 *
 * Can only be used as admin
 *
 * @param string $key option key to set
 * @param mixed $value option value to set
 */
function set_survey_option($key,$value)
{
  if( is_admin() ) {
    $options = get_option(OPTIONS_KEY,array());
    $options[$key] = $value;
    update_option(OPTIONS_KEY,$options);
  }
}

/**
 * get URI for pdf of the survey
 * @return uri for link to pdf of the current survey
 */
function survey_pdf_uri() {
  return get_survey_option(PDF_URI_KEY);
}

/**
 * get (wordpress) user capabilities
 * @return list of capabilities
 */
function survey_capabilities() {
  return get_survey_option(CAPS_KEY);
}

/**
 * get survey log level
 * @return DEV, INFO, WARNING, or ERROR
 */
function survey_log_level() {
  return get_survey_option(LOG_LEVEL_KEY);
}

/**
 * reset option value
 *
 * Can only be used as admin
 *
 * @param string $key option to reset
 */
function reset_survey_option($key) 
{
  if( is_admin() ) {
    $options = get_option(OPTIONS_KEY,array());
    unset($options[$key]);
    update_option(OPTIONS_KEY,$options);
  }
}

/**
 * reset all sruvey options
 *
 * Can only be used as admin
 **/
function reset_all_survey_options()
{
  if( is_admin() ) {
    update_option(OPTIONS_KEY,array());
  }
}

/**
 * removes plugin settings from the WP database
 */
function uninstall_options()
{
  delete_option(OPTIONS_KEY);
}

/**
 * update options from update post
 */
function update_options_from_post()
{
  if (!wp_verify_nonce($_POST['_wpnonce'],OPTIONS_NONCE)) {
    log_error("failed to validate nonce");
    wp_die("Bad nonce");
  }

  $options = get_option(OPTIONS_KEY,array());

  $new_caps = $_POST['caps'];
  $options[CAPS_KEY] = $new_caps;

  $options[LOG_LEVEL_KEY] = strtoupper($_POST['log_level']);

  $options[PDF_URI_KEY] = sanitize_url(
    $_POST['pdf_uri'],
    ['http','https','ftp','ftps'],
  );

  foreach(get_users() as $user) {
    $id = $user->id;
    foreach(['responses','content'] as $cap) {
      $key = "tlc-ttsurvey-$cap";
      if($new_caps[$cap][$id]) {
        $user->add_cap($key);
      } else {
        $user->remove_cap($key);
      }
    }
  }
  update_option(OPTIONS_KEY,$options);
}


