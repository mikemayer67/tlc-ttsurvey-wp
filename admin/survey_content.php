<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

if( !current_user_can('tlc-ttsurvey-content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/surveys.php');

log_dev("SERVER: ".print_r($_SERVER,true));

add_noscript_body();
add_script_body();

function add_noscript_body()
{
  echo("<noscript class=warning>");
  echo("<p>Managing survey content requires that Javascript be enabled</p>");
  echo("</noscript>");
}

function add_script_body()
{
  $current = current_survey();
  echo("<div class='requires-javascript'>");
  add_current_survey_info($current);
  add_survey_content_tabs($current);
  echo("</div>");
}

function add_current_survey_info($current)
{
  echo("<div class='tlc-survey-status'>");
  if($current) {
    $name = $current['name'];
    $status = $current['status'];
    echo("<h2>Current Survey</h2>");
    if($status==SURVEY_IS_ACTIVE) {
      echo("<div class=info>");
      echo("The $name Time and Talent Survey is currently open. ");
      echo("</div><div class=info>");
      echo("No changes can be made to its content without moving it back ");
      echo("to draft status.");
      echo("</div>");
    } else {
      echo("<div class=info>");
      echo("The $name Time and Talent Survey is in draft status.");
      echo("</div>");
      if(survey_response_count($current['post_id']) > 0) {
        echo("<div class='info warning'>");
        echo("Responses to the survey have been submitted.");
        echo(" Be very careful when making changes to the form.");
        echo("</div>");
      }
    }
  } 
  else 
  {
    $action = $_SERVER['REQUEST_URI'];
    echo("<h2>Create or Reopen a Survey</h2>");
    echo("<form id='tlc-new-survey' class=tlc action='$action' method=POST>");
    wp_nonce_field(OPTIONS_NONCE);
    echo("<input type=hidden name=action value=start-survey>");
    echo("<span class=option>");
    echo("<span class=label>There is no currently active or draft survey: </span>");
    echo("<select class=tlc name=option>");
    echo("<option value=''>That's fine...</option>");
    echo("<option value=new>Create a new survey</option>");
    foreach(survey_catalog() as $post_id=>$survey) {
      if($survey['status'] == SURVEY_IS_CLOSED) {
        $name = $survey['name'];
        echo("<option value='reopen-$post_id'>Reopen $name survey</option>");
      }
    }
    echo("</select>");
    echo("</span>");
    echo("</form>");
  }
  echo("</div>"); // div.tlc-survey-status
}


function add_survey_content_tabs($current)
{
  echo("<h2>Add survey tabs here</h2>");
}

