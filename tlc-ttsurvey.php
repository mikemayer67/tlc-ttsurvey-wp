<?php
namespace TLC\TTSurvey;

/**
 * Plugin Name: TLC Time and Talent Survey
 * Plugin URI: https://github.com/mikemayer67/tlc-ttsurvey
 * Description: Time and Talent Survey WP Plugin
 * Version: 0.0.2
 * Author: Michael A. Mayer
 * Requires PHP: 7.3.0
 * License: GPLv3
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

if( ! defined('WPINC') ) { die; }

/**
 * scope the specified string to the plugin namespace
 *
 * @param string $name function, variable, class, etc. in plugin namespace
 * @return string namespace scoped name
 */
function ns($s)
{
  return __NAMESPACE__.'\\'.$s;
}

/**
 * return absolute path to the plugin file
 * 
 * @return absolute path to plugin file
 */
function plugin_file()
{
  return __FILE__;
}

/**
 * return absolute path to the plugin directory
 * 
 * @return absolute path to plugin directory
 */
function plugin_dir()
{
  return plugin_dir_path(__FILE__);
}

/**
 * Converts path relative to the plugin directory to an absoute path
 *
 * @param path relative to the plugin directory
 * @return absolute path
 */
function plugin_path($path)
{
  return plugin_dir() . '/' . $path;
}

/**
 * return url to a plugin resource
 * 
 * @param resource path relative to plugin directory
 * @return string url to plugin resource
 */
function plugin_url($rel_url)
{
  return plugin_dir_url(__FILE__).'/'.$rel_url;
}

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/users.php');

/**
 * plugin activation hooks
 */

function handle_activate()
{
  log_info('activate: '.__NAMESPACE__);
  users_activate();
  surveys_activate();
}

function handle_deactivate()
{
  log_info('deactivate: '.__NAMESPACE__);
  users_deactivate();
  surveys_deactivate();
}

function handle_uninstall()
{
  log_info('uninstall: '.__NAMESPACE__);
  uninstall_options();
}

register_activation_hook(   __FILE__, ns('handle_activate') );
register_deactivation_hook( __FILE__, ns('handle_deactivate') );
register_uninstall_hook(    __FILE__, ns('handle_uninstall') );

const SURVEY_STATUS_INFO = 0;
const SURVEY_STATUS_WARNING = 1;
const SURVEY_STATUS_ERROR = 2;
$survey_status = null;
function set_survey_status($msg,$level)
{
  global $survey_status;
  $survey_status = [$level,$msg];
}
function set_survey_info($msg) { set_survey_status($msg,SURVEY_STATUS_INFO); }
function set_survey_warning($msg) { set_survey_status($msg,SURVEY_STATUS_WARNING); }
function set_survey_error($msg) { set_survey_status($msg,SURVEY_STATUS_ERROR); }

$shortcode_page = null;
function set_shortcode_page($page)
{
  global $shortcode_page;
  $shortcode_page=$page;
}


if( is_admin() ) /* Admin setup */
{
  require_once plugin_path('admin/setup.php');
}
else /* Non-admin setup */
{
  require_once plugin_path('include/login.php');
  require_once plugin_path('shortcode.php');
  require_once plugin_path('ajax.php');
}
