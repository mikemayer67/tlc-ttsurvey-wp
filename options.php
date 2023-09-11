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
const ACTIVE_YEAR_KEY = 'active_year';
const PDF_URI_KEY = 'pdf_href';

$option_defaults = array(
  CAPS_KEY => [],
  ACTIVE_YEAR_KEY => '',
  PDF_URI_KEY => '',
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
 * get active survey year
 * @return active survey year
 */
function active_year() {
  return get_survey_option(ACTIVE_YEAR_KEY);
}

/**
 * get URI for pdf of the survey
 * @return uri for link to pdf of the current survey
 */
function pdf_uri() {
  return get_survey_option(PDF_URI_KEY);
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
  $options[ACTIVE_YEAR_KEY] = $_POST['active_year'];

  $new_caps = $_POST['caps'];
  $options[CAPS_KEY] = $new_caps;

  $new_pdf_uri = $_POST['pdf_uri'];
  log_info("new_pdf_uri: $new_pdf_uri");
  $new_pdf_uri = sanitize_url($new_pdf_uri,['http','https','ftp','ftps']);
  log_info("(sanitized): $new_pdf_uri");
  $otions[PDF_URI_KEY] = $new_pdf_uri;

  foreach(get_users as $user) {
    $id = $user->id;
    foreach(['responses','structure'] as $cap) {
      $key = "tlc-ttsurvey-$cap";
      if($new_caps[$cap][$id]) {
        $user->add_cap($key);
      } else {
        $user->remove_cap($key);
      }
    }
  }
}
