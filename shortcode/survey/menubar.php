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
  $fullname = $user->fullname();

  $status = "Status";

  echo "<div id='tlc-ttsurvey-menu'>";

  echo "<div id='tlc-ttsurvey-menubar' class='menubar'>";
  echo "<div class='menuitem survey-name'>$survey_name Time & Talent Survey</div>";
  echo "<div class='menuitem status'>$status</div>";

  echo "<div class='menuitem user'>";
  echo "<span class='userid'>$fullname</span>";
  add_user_menu();
  echo "</div>"; // menuitem.user
  echo "</div>"; // menubar
  add_profile_editor($user);
  echo "</div>"; // menu
  echo "<div class='spacer'></div>";
}

function add_user_menu()
{
  echo "<span class='user-menu'>";

  echo "<input id='user-menu-toggle' type='checkbox' class='trigger'>";

  $icon = plugin_url('img/icons8-menu.png');
  echo "<label for='user-menu-toggle'>";
  echo "<img class='trigger' src='$icon' width=18 height=18>";
  echo "</label>";

  echo "<div class='menu'>";
  echo "<div>hello</div>";
  echo "<div>";
  echo "<label for='profile-editor-toggle'>Log out</label>";
  echo "</div>";
  echo "</div>";

  echo "</span>";
}

function add_profile_editor($user)
{
  $fullname = $user->fullname();
  $email = $user->email() ?? "";

  echo "<input id='profile-editor-toggle' type='checkbox'>";

  echo "<div class='profile-editor'>";
  echo "<p>Profile Editor</p>";
  // End of user menu
  echo "</div>";
}
