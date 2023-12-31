<?php
namespace TLC\TTSurvey;

require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

$current = current_survey();

if($current)
{
  $name = $current->name();
  echo "<div id='status-message' class='card info'>";
  echo "<p>The $name Time and Talent Survey is under construction</p>";
  echo "</div>";
}
else 
{
  echo "<div id='status-message' class='card warning'>";
  echo "<p>There are currently no active Time and Talent surveys</p>";
  echo "</div>";
}
