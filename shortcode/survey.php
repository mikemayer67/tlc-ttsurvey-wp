<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/surveys.php');

wp_enqueue_style('tlc-ttsurvey-survey', plugin_url('shortcode/css/survey.css'));

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

  return true;
}

function add_survey_menubar($userid)
{
  $survey = current_survey();
  $survey_name = $survey->name() . " Time & Talent Survey";

  $user = User::from_userid($userid);
  $fullname = $user->fullname();

  $status = "Status";

  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  $icon_url = plugin_url('/img/icons8-down.png');

  echo <<<MENUBAR_HTML
    <nav class='menubar'>
      <div class='menubar-item survey-name'>$survey_name</div>
      <div class='menubar-item status'>$status</div>
      <div class='menubar-item user'>
      <button class='menu-btn user'>$fullname<img src='$icon_url'></button>
        <div class='menu user'>
            <a href='' data-action='edit-profile'>Edit Profile</a>
            <a href='$form_uri?logout=1'>Log Out</a>
        </div>
      </div>
    </nav>
    MENUBAR_HTML;
}

function add_user_profile_editor($userid)
{
  echo <<<PROFILE_EDITOR_HTML
    <div class='modal user-profile'>
      <div class='dialog-box user-profile'>
        <p>Hello there</p>
        <a href=''>Cancel</a>
      </div>
    </div>
    PROFILE_EDITOR_HTML;
}

function enqueue_survey_script()
{
  wp_register_script(
    'tlc_ttsurvey_script',
    plugin_url('shortcode/js/survey.js'),
    array('jquery'),
    '1.0.3',
    true
  );

  wp_localize_script(
    'tlc_ttsurvey_script',
    'survey_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('survey',wp_create_nonce('survey')),
    ),
  );

  wp_enqueue_script('tlc_ttsurvey_script');
}

