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
  $survey_name = $survey->name() . " Time & Talent Survey";

  $user = User::from_userid($userid);
  $fullname = $user->fullname();

  $status = "Status";

  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  $icon_url = plugin_url('/img/icons8-down.png');
?>

<nav class='menubar'>
  <div class='menubar-item survey-name'><?=$survey_name?></div>
  <div class='menubar-item status'><?=$status?></div>
  <div class='menubar-item user'>
  <button class='menu-btn user'><?=$fullname?><img src='<?=$icon_url?>'></button>
    <div class='menu user'>
        <a href='#' data-action='edit-profile'>Edit Profile</a>
        <a href='<?=$form_uri?>?logout=1'>Log Out</a>
    </div>
  </div>
</nav>

<?php
}

//function add_survey_menubar($userid)
//{
//  $survey = current_survey();
//  $survey_name = $survey->name();
//
//  $user = User::from_userid($userid);
//
//  $status = "Status";
//
//  echo "<div class='menubar-box'>";
//  echo "<div class='menubar'>";
//  echo "<div class='menubar-item survey'>$survey_name Time & Talent Survey</div>";
//  echo "<div class='menubar-item status'>$status</div>";
//
//  add_user_menu($user);
//
//  echo "</div>"; // menubar
//  add_profile_editor($user);
//  echo "</div>"; // menubar-box
//  echo "<div class='spacer'></div>";
//}
//
//function add_user_menu($user)
//{
//  $fullname = $user->fullname();
//  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
//
//  echo "<div class='menubar-item user'>";
//  echo "<span>$fullname</span>";
//  echo "<div class='menu'>";
//  echo "<ul>";
//  echo "<li><a class='menu-item'>Edit Profile</a></li>";
//  echo "<li><a href='$form_uri?logout=1'>Log Out</a></li>";
//  echo "</ul>";
//  echo "</div>"; // menu
//  echo "</div>"; // menuitem
//}
//
//function add_profile_editor($user)
//{
//  $fullname = $user->fullname();
//  $email = $user->email() ?? "";
//
//  echo "<div class='profile-editor'>";
//  echo "<p>Profile Editor for $fullname</p>";
//  echo "<div><a class='edit-profile-cancel'>Cancel</a></div>";
//  echo "</div>";
//}
