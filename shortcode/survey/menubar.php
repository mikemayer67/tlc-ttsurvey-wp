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

  echo "<div id='tlc-ttsurvey-menubar' class='menubar'>";
  echo "<div class='menuitem survey-name'>$survey_name Time & Talent Survey</div>";
  echo "<div class='menuitem status'>$status</div>";
  echo "<div class='menuitem userid'>$fullname</div>";
  echo "</div>";
  echo "<div class='spacer'></div>";
}
