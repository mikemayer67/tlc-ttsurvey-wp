<?php
namespace TLC\TTSurvey;

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if( !plugin_admin_can('responses') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

?>

<h2>Current Survey Responses</h2>
