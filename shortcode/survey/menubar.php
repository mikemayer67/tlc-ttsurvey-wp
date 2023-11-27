<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/surveys.php');

wp_enqueue_style('tlc-ttsurvey-survey', plugin_url('shortcode/css/survey.css'));

function add_survey_menubar($userid)
{
  $survey = current_survey();
  $survey_name = $survey['name'];

  $user = User::from_userid($userid);

  $status = "Status";

  echo "<div class='menubar-box'>";
  echo "<div class='menubar'>";
  echo "<div class='menuitem survey'>$survey_name Time & Talent Survey</div>";
  echo "<div class='menuitem status'>$status</div>";

  add_user_menu($user);

  echo "</div>"; // menubar
  add_profile_editor($user);
  echo "</div>"; // menubar-box
  echo "<div class='spacer'></div>";

  add_javascript_recommended();
}

function add_user_menu($user)
{
  $fullname = $user->fullname();
  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  echo "<div class='menuitem user'>";
  echo "<span>$fullname</span>";
  echo "<div class='menu'>";
  echo "<ul>";
  echo "<li><label for='profile-editor-toggle'>Edit Profile</label></li>";
  echo "<li><a href='$form_uri?logout=1'>Log Out</a></li>";
  echo "</ul>";
  echo "</div>"; // menu
  echo "</div>"; // menuitem
}

function add_profile_editor($user)
{
  $fullname = $user->fullname();
  $email = $user->email() ?? "";

  echo "<input id='profile-editor-toggle' type='checkbox' class='toggle'>";

  echo "<div class='profile-editor'>";
  echo "<noscript>";
  echo "<p class='noscript'>Because Javascript is disabled, your unsaved responses ";
  echo "to the survey will be lost when you update your profile.</p>";
  echo "</noscript>";
  echo "<p>Profile Editor for $fullname</p>";
  echo "<div><label for='profile-editor-toggle' class='cancel'>Cancel</label></div>";
  echo "</div>";
}
