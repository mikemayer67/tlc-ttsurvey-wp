<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

require_once plugin_path('include/logger.php');

wp_enqueue_style('tlc-ttsurvey-survey', plugin_url('shortcode/css/survey.css'));

function add_survey_menubar($userid)
{
  echo "<div class='menubar'>";
  echo "Menubar";
  echo "</div>";
  echo "<div class='menubar spacer'></div>";
}
