<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

const SURVEY_FORM_NONCE = 'tlc-ttsurvey-survey-form';

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

  echo "<h2>Survey</h2>";
  echo "<form method='post' action='$form_uri'>";
  // Yes, we want the login nonce here as logout is a "login" action
  wp_nonce_field(LOGIN_FORM_NONCE);
  echo "  <input type='hidden' name='action' value='logout'>";
  echo "  <input type='submit' value='Log Out'>";
  echo "</form>";

  return true;
}


function enqueue_survey_script()
{
  log_dev("enqueue_survey_script");

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

