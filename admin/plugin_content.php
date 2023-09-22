<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

if( !current_user_can('tlc-ttsurvey-content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}
?>

<noscript class=warning>
<p>Managing survey content requires that Javascript be enabled</p>
</noscript>

<div class='requires-javascript'>
  <h2>Create/View/Edit Survey Content</h2>
  <h3>Concept:</h3>
  <ul style="list-style:initial;margin-left:2em;">
    <li>Create tabs for each year</li>
    <li>Only allow edit of currnet year</li>
    <li>Display form structure as yaml</li>
    <li>For current year, provide select button for setting status</li>
    <li>Validate yaml content on submit</li>
  </ul>
</div>
