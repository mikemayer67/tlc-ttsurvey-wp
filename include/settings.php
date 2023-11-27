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

require_once plugin_path('include/const.php');

const OPTIONS_KEY = 'tlc_ttsurvey_options';

const CAPS_KEY = 'caps';
const PDF_URI_KEY = 'pdf_href';
const LOG_LEVEL_KEY = 'log_level';
const SURVEY_POST_UI_KEY = 'survey_post_ui';
const USER_POST_UI_KEY = 'user_post_ui';
const PRIMARY_ADMIN_KEY = 'primary_admin';

$option_defaults = array(
  CAPS_KEY => [],
  PDF_URI_KEY => '',
  LOG_LEVEL_KEY => 'INFO',
  SURVEY_POST_UI_KEY => 'NONE',
  USER_POST_UI_KEY => 'NONE',
  PRIMARY_ADMIN_KEY => '',
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
 * get userid of the primary admin
 * @return userid of the primary admin
 */
function survey_primary_admin() {
  return get_survey_option(PRIMARY_ADMIN_KEY);
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

function survey_admins($role) 
{
  $caps = survey_capabilities();
  $users = $caps[$role] ?? array();
  $rval = array_keys($users);

  if($role == 'manage') {
    foreach( get_users() as $user )
    {
      $id = $user->ID;
      if(user_can($id,'manage_options')) {
        if(!in_array($id,$rval)) {
          $rval[] = $id;
        }
      }
    }
  }
  return $rval;
}

function survey_admin_contacts($role) 
{
  if($role === 'general') {
    $primary = survey_primary_admin();
    $contacts = array($primary);
    foreach(survey_admins('responses') as $uid) {
      if($uid != $primary) { $contacts[] = $uid; }
    }
  } 
  else 
  {
    $contacts = survey_admins($role);
  }

  $rval = array();
  foreach($contacts as $id) {
    $user = get_user_by('ID',$id);
    $name = $user->display_name;
    $email = $user->user_email;
    $rval[] = "<a href='mailto:$email?subject=Time and Talent Survey'>$name</a>";
  }

  if(count($rval) == 0) { return ""; }
  if(count($rval) == 1) { return $rval[0]; }
  if(count($rval) == 2) { return "$rval[0] or $rval[1]"; }

  $last = array_pop($rval);
  return implode(', ',$rval) . ", or $last";
}

/**
 * get survey log level
 * @return LOGGER_DEV, LOGGER_INFO, LOGGER_WARNING, or LOGGER_ERROR
 */
function survey_log_level() {
  return get_survey_option(LOG_LEVEL_KEY);
}

/**
 * get survey post UI
 * @return POST_UI_NONE, POST_UI_POSTS, POST_UI_TOOLS,
 */
function survey_post_ui() {
  return get_survey_option(SURVEY_POST_UI_KEY);
}

function user_post_ui() {
  return get_survey_option(USER_POST_UI_KEY);
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
 * reset all survey options
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
  $options = get_option(OPTIONS_KEY,array());

  $new_caps = $_POST['caps'];
  $options[CAPS_KEY] = $new_caps;
  $options[PRIMARY_ADMIN_KEY] = $_POST['primary_admin'];

  $options[LOG_LEVEL_KEY] = strtoupper($_POST['log_level']);
  $options[SURVEY_POST_UI_KEY] = strtoupper($_POST['survey_post_ui']);
  $options[USER_POST_UI_KEY] = strtoupper($_POST['user_post_ui']);

  $options[PDF_URI_KEY] = sanitize_url(
    $_POST['pdf_uri'],
    ['http','https','ftp','ftps'],
  );

  foreach(get_users() as $user) {
    $id = $user->ID;
    $view = false;
    foreach(['manage','responses','content','tech','data'] as $cap) {
      $key = "tlc-ttsurvey-$cap";
      if($new_caps[$cap][$id] ?? False) {
        $user->add_cap($key);
        $view = true;
      } else {
        $user->remove_cap($key);
      }
    }
    if($view) {
      $user->add_cap('tlc-ttsurvey-view');
    } else {
      $user->remove_cap('tlc-ttsurvey-view');
    }
  }
  update_option(OPTIONS_KEY,$options);

  require_once plugin_path('include/surveys.php');
  require_once plugin_path('include/users.php');
  register_survey_post_type();
  register_userid_post_type();
}


