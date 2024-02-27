<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('shortcode/menubar.php');

wp_enqueue_style('tlc-ttsurvey-survey', plugin_url('shortcode/css/survey.css'));
wp_enqueue_style('tlc-ttsurvey-menubar', plugin_url('shortcode/css/menubar.css'));

function add_survey_content($userid=null)
{
  if(!$userid) {
    require_once plugin_path('include/login.php');
    $userid = active_userid();
    if(!$userid) {
      log_warning("survey content was requested without an active user");
      unset($_GET['tlcpage']);
      clear_current_shortcode_page();
      return false;
    }
  }

  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  echo "<div id='survey'>";

  add_survey_menubar($userid);
  add_user_profile_editor($userid);

  for($x=0; $x<=20; $x++) {
    echo "<p>Line $x</p>";
  }
  echo "</div>";
  echo "</form>";

  for($x=0; $x<=20; $x++) {
    echo "<p>Post Line $x</p>";
  }

  enqueue_menubar_script();
//  enqueue_survey_script();

  return true;
}
