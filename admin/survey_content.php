<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

if( !current_user_can('tlc-ttsurvey-content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

const FIRST_TAB = 'first';

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
  $active_pid = determine_content_tab($current);
  add_survey_tab_bar($active_pid,$current);
  add_survey_tab_content($active_pid,$current);

  echo "</div>";
}

function determine_content_tab($current)
{
  // if post_id (pid) was specified as part of the GET request, honor it
  //   note that if value of pid is 'first', we need to resolve that to
  //   the current post_id if there is a current survey
  $pid = $_GET['pid'] ?? null;
  if($pid)
  {
    if($pid == FIRST_TAB) { 
      return $current['post_id'] ?? FIRST_TAB;
    }
    else {
      return $pid;
    }
  }

  // no pid was specified as part of the GET request.
  //   Show the current survey if there is one
  if($current) {
    return $current['post_id'];
  }

  // no pid specified and no current survey
  //   Show the newest entry in the survey catalog
  $catalog = survey_catalog();
  if($catalog) {
    krsort($catalog);
    return array_key_first($catalog);
  }

  // no pid specified, no current survey, and no survey catalog
  //   Only option is to create a new survey (i.e. first and only tab)
  return FIRST_TAB;
}

function add_survey_tab_bar($active_pid,$current)
{
  echo "<div class=nav-tab-wrapper>";

  $query_args = array();
  $uri_path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$query_args);

  // construct array of tabs
  $tabs = array();

  //   first tab is current survey if there is a current survey
  //     otherwise, it's the new survey tab
  if($current) {
    $tabs[] = array($current['name'],$current['post_id']);
  } else {
    $tabs[] = array(' + ',FIRST_TAB);
  }

  // remaning tabs come from the survey catalog (skipping current survey)
  foreach( survey_catalog() as $pid=>$survey )
  {
    if($pid != $current['post_id'])
    {
      $tabs[] = array($survey['name'],$pid);
    }
  }

  // populate the tabs
  foreach($tabs as $tab)
  {
    [$label,$pid] = $tab;
    $class = $pid == $active_pid ? 'nav-tab nav-tab-active' : 'nav-tab';
    $query_args['pid'] = $pid;
    $uri = implode('?', array($uri_path,http_build_query($query_args)));
    echo "<a class='$class' href='$uri'>$label</a>";
  }

  echo "</div>";
}

function add_survey_tab_content($active_pid,$current)
{
  $current_pid = $current['post_id'] ?? '';
  if($active_pid == FIRST_TAB)
  {
    add_new_survey_content();
  } 
  elseif( $active_pid == $current_pid )
  {
    add_current_survey_content($current);
  }
  else
  {
    add_past_survey_content($active_pid,$current);
  }
}

function add_new_survey_content()
{
  $action = $_SERVER['REQUEST_URI'];

  $existing_names = array();
  foreach(survey_catalog() as $pid=>$survey) {
    $existing_names[] = $survey['name'];
  }

  $cur_year = date('Y');
  $suggested_name = "$cur_year";
  $n = 2;
  while(in_array($suggested_name,$existing_names))
  {
    $suggested_name = "$suggested_name-$n";
    ++$n;
  }
  $existing_names = json_encode($existing_names);

  echo "<div class=tlc-ttsurvey-new>";
  echo "  <h2>Create a New Survey</h2>";
  echo "  <form class='tlc new-survey' action=$action method=POST>";
  echo "    <input type=hidden name=action value=new-survey>";
  echo "    <input class=existing type=hidden value='$existing_names'>";
  echo "    <span class=new-name>";
  echo "      <span class=label>Survey Name</span>";
  echo "      <input type=text class=new-name name=name value=$suggested_name>";
  echo "      <span class=error></span>";
  echo "    </span>";
  echo "    <div>";
  $class = "class='submit button button-primary button-large'";
  echo "      <input type=submit value='Create Survey' $class'>";
  echo "    </div>";
  echo "  </form>";
  echo "</div>";
}

function add_current_survey_content($current)
{
  print_r($current);
}

function add_past_survey_content($pid,$current)
{
  echo($pid);
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
