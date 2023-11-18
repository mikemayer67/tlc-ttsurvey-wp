<?php
namespace TLC\TTSurvey;

require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

$current = current_survey();

echo "<div class='status card warning'>";
if($current)
{
  $name = $current['name'];
  echo "<p>The $name Time and Talent Survey is under construction</p>";
}
else 
{
  echo "<p>There are currently no active Time and Talent surveys</p>";
}
echo "</div>";
