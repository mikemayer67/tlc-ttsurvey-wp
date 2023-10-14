<?php
namespace TLC\TTSurvey;

require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

$current = current_survey();

if($current)
{
  $name = $current['name'];
  echo "<div class='w3-container w3-panel w3-card-4 w3-pale-yellow w3-leftbar w3-border-yellow w3-border'>";
  echo "<p class='w3-margin w3-xlarge'>The $name Time and Talent Survey is under construction</p>";
  echo "</div>";
}
else 
{
  echo "<div class='w3-container w3-panel w3-card-4 w3-pale-yellow w3-leftbar w3-border-yellow w3-border'>";
  echo "<p class='w3-margin w3-xlarge'>There are currently no active Time and Talent surveys</p>";
  echo "</div>";
}
