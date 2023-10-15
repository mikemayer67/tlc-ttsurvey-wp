<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');

$userid = active_userid();
if(!$userid)
{
  log_warning("survey content was requested without an active user");
  unset($_GET['tlcpage']);
  clear_current_shortcode_page();
  add_shortcode_content();
  return;
}

$form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

echo "<h2>Survey</h2>";
echo "<form method='post' action='$form_uri'>";
wp_nonce_field(LOGIN_FORM_NONCE);
echo "  <input type='hidden' name='action' value='logout'>";
echo "  <input type='submit' value='Log Out'>";
echo "</form>";
