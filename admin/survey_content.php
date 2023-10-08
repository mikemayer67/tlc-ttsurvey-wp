<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

if( !current_user_can('tlc-ttsurvey-content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/surveys.php');

add_noscript_body();
add_script_body();

function add_noscript_body()
{
  echo "<noscript class=warning>";
  echo "<p>Managing survey content requires that Javascript be enabled</p>";
  echo "</noscript>";
}

function add_script_body()
{
  $current = current_survey();
  echo "<div class=requries-javascript>";
  $active_tab = determine_content_tab($current);
  add_survey_tab_bar($active_tab,$current);
  add_survey_tab_content($active_tab,$current);

  echo "</div>";
}

function determine_content_tab($current)
{
  $sid = $_GET['sid'] ?? null;
  if($sid) {
    return $sid;
  }
  if($current) {
    return 'first';
  }

  $catalog = survey_catalog();
  if(count($catalog) < 2) {
    return 'first';
  }

  krsort($catalog);
  return array_key_first($catalog);
}

function add_survey_tab_bar($active_tab,$current)
{
  echo "<div class=nav-tab-wrapper>";

  $query_args = array();
  $uri_path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$query_args);

  // first tab (current or create)
  $class = $active_tab == "first" ? 'nav-tab nav-tab-active' : 'nav-tab';
  $tab_label = $current['name'] ?? ' + ';
  $query_args['sid'] = 'first';
  $uri = implode('?', array($uri_path,http_build_query($query_args)));
  echo "<a class='$class' href='$uri'>$tab_label</a>";

  //Prior surveys
  
  foreach( survey_catalog() as $post_id=>$survey )
  {
    if($post_id != $current['post_id']) {
      $class = $post_id==$active_tab ? 'nav-tab nav-tab-active' : 'nav-tab';
      $name = $survey['name'];
      $query_args['sid'] = $post_id;
      $uri = implode('?', array($uri_path,http_build_query($query_args)));
      echo "<a class='$class' href='$uri'>$name</a>";
    }
  }

  echo "</div>";
}

function add_survey_tab_content($active_tab,$current)
{
  echo($active_tab);
}

/*
function add_current_survey_info($current)
{
  echo "<div class='tlc-survey-status'>";
  if($current) {
    $name = $current['name'];
    $status = $current['status'];
    echo "<h2>Current Survey</h2>";
    if($status==SURVEY_IS_ACTIVE) {
      echo "<div class=info>";
      echo "The $name Time and Talent Survey is currently open. ";
      echo "</div><div class=info>";
      echo "No changes can be made to its content without moving it back ";
      echo "to draft status.";
      echo "</div>";
    } else {
      echo "<div class=info>";
      echo "The $name Time and Talent Survey is in draft status.";
      echo "</div>";
      if(survey_response_count($current['post_id']) > 0) {
        echo "<div class='info warning'>";
        echo "Responses to the survey have been submitted.";
        echo " Be very careful when making changes to the form.";
        echo "</div>";
      }
    }
  } 
  else 
  {
    $action = $_SERVER['REQUEST_URI'];
    echo "<h2>Create or Reopen a Survey</h2>";
    echo "<form class='tlc new-survey' action='$action' method=POST>";
    wp_nonce_field(OPTIONS_NONCE);
    echo "<input type=hidden name=action value=start-survey>";
    echo "<span class=option>";
    echo "<span class=label>There is no currently active or draft survey: </span>";
    echo "<select class=tlc name=option>";
    echo "<option value=''>That's fine...</option>";
    echo "<option value=create>Create a new survey</option>";
    foreach(survey_catalog() as $post_id=>$survey) {
      if($survey['status'] == SURVEY_IS_CLOSED) {
        $name = $survey['name'];
        echo "<option value='reopen-$post_id'>Reopen $name survey</option>";
      }
    }
    $current_year = date('Y');
    echo "</select>";

    echo "<span class=new-name>";
    echo "with name:";
    echo "<input type=text class=new-name name=name value=$current_year>";
    echo "</span>";

    echo "</span>";
    echo "<div>";
    echo "<input type=submit value=Proceed class='submit button button-primary button-large'>";
    echo "</div>";
    echo "</form>";
  }
  echo "</div>"; // div.tlc-survey-status
}


function add_survey_content_tabs($current)
{
  echo "<h2>Add survey tabs here</h2>";
}
*/
