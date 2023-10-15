<?php
namespace TLC\TTSurvey;

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if( !plugin_admin_can('manage') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');

echo "<div class='log'>";

$entries = array();
$entry_re = '/^\[(.*?)\]\s*(\w+)\s*(.*?)\s*$/';
foreach(file(plugin_path(PLUGIN_LOG_FILE)) as $line) {
  $m = array();
  if(preg_match($entry_re,$line,$m))
  {
    $entry = "<tr class='" . strtolower($m[2]). "'>";
    $entry .= "<td class='date'>" . $m[1] . "</td>";
    $entry .= "<td class='message'>" . $m[3] . "</td>";
    $entry .= "</tr>";
    $entries[] = $entry;
  }
  echo "<table>";
  foreach (array_reverse($entries) as $entry) {
    echo $entry;
  }
  echo "</table>";
}
echo "</div>";

$action = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
echo "<form action='$action' method='POST'>";
echo "  <input type='hidden' name='action' value='clear-log'>";
wp_nonce_field(OPTIONS_NONCE);
$class = 'submit button button-primary button-large';
echo "  <input type='submit' value='Clear Log' class='$class'>";
echo "</form>";
$href = plugin_url(PLUGIN_LOG_FILE);
echo "<div><a href='$href' target='_blank'>View Log File</a></div>";

