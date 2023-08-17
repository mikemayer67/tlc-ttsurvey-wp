<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

if( !current_user_can('tlc-ttsurvey-structure') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

?>

<h2>Configure Survey Content</h2>
