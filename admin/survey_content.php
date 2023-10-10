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
  echo "<div class='tlc-ttsurvey-content requires-javascript'>";
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
  echo "<div class='nav-tab-wrapper survey'>";

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
    $suggested_name = "$cur_year-$n";
    ++$n;
  }
  $existing_names = json_encode($existing_names);

  echo "<div class=new>";
  echo "  <h2>Create a New Survey</h2>";
  echo "  <form class='tlc new-survey' action=$action method=POST>";
  wp_nonce_field(OPTIONS_NONCE);
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
  echo "<div class=current>";

  $name = $current['name'];
  $status = $current['status'];
  if($status == SURVEY_IS_ACTIVE) {
    echo "<div class=info>";
    echo "<div> The $name Time and Talent Survey is currently open. ";
    echo "</div><div>";
    echo "No changes can be made to its content without moving it back ";
    echo "to Draft status on the Settings tab.";
    echo "</div></div>";
    add_immutable_survey_content($current);
  }
  elseif($status == SURVEY_IS_DRAFT) {
    echo "<div class=info>";
    echo "<div> The $name Time and Talent Survey is currently in draft mode.";
    echo "</div><div>";
    echo "To lock in its structure and open it for participation, switch its status";
    echo " to Active on the Settings tab.";
    echo "</div></div>";
    add_mutable_survey_content($current);
  }

  echo "</div>";
}

function add_past_survey_content($pid,$current)
{
  echo "<div class=past>";

  $survey = survey_catalog()[$pid] ?? null;
  if(!$survey) { 
    log_error("Attempted to show content for invalid pid ($pid)");
    return null;
  }

  if(!$current) {
    $action = $_SERVER['REQUEST_URI'];
    echo "<form class='tlc reopen-survey' action=$action method=POST>";
    wp_nonce_field(OPTIONS_NONCE);
    echo "<input type=hidden name=action value=reopen-survey>";
    echo "<input type=hidden name=pid value=$pid>";
    echo "<input type=submit value='Reopen survey'>";
    echo "</form>";
  }

  $name = $survey['name'];
  echo "<div class=info>";
  echo "<div> The $name Time and Talent Survey is currently closed. ";
  echo "</div><div>";
  echo "No changes can be made to its content.";
  echo "</div></div>";

  add_immutable_survey_content($survey);

  echo "</div>";
}

function add_immutable_survey_content($survey)
{
  add_survey_content($survey,false);
}

function add_mutable_survey_content($survey)
{
  $action = $_SERVER['REQUEST_URI'];
  $pid = $survey['post_id'];

  echo "<form class='tlc edit-survey' action=$action method=POST>"; 
  wp_nonce_field(OPTIONS_NONCE);
  echo "<input type=hidden name=action value=update-survey>";
  echo "<input type=hidden name=pid value=$pid>";

  add_survey_content($survey,true);

  $class = "class='submit button button-primary button-large'";
  echo "<input type=submit value=Save $class>";
  echo "</form>";
}

function add_survey_content($survey,$mutable)
{
  $name = $survey['name'];

  $readonly = $mutable ? "" : "readonly";

  $pid = $survey['post_id'];

  $post = get_post($pid);
  if(!$post) {
    log_warning("Attempted to add survey content for invalid post_id=$pid");
    return null;
  }

  $content = $post->post_content;
  $content = json_decode($content,true);

  echo "<div class=content-block>";

  $data = $content['survey'] ?? '';
  echo "<h2>Survey Form</h2>";
  echo "<div class=info>";
  echo "Instructions go here.";
  echo "<textarea class='survey' name=survey $readonly>$data</textarea>";
  echo "</div>";

  echo "<h2>Email Templates</h2>";
  echo "<div class=info>";
  echo "All email templates use markdown notation.  For more information, visit ";
  echo "the <a href='https://www.markdownguide.org/basic-syntax' target=_blank>";
  echo "Markdown Guid</a>.";
  echo "</div>";
  echo "<div class=info>";
  echo "In addition, the following placeholders may be used to customize the message.";
  echo "</div>";
  echo "<table class=info>";
  echo "<tr><td>&lt;&lt;name&gt;&gt;</td><td>Recipient's full name</td></tr>";
  echo "<tr><td>&lt;&lt;email&gt;&gt;</td><td>Recipient's email addrress</td></tr>";
  echo "<tr><td>&lt;&lt;token&gt;&gt;</td><td>Recipient's access token</td></tr>";
  echo "</table>";

  $welcome = $content['welcome'];
  echo "<div class=email-template>";
  echo "<h3>Welcome</h3>";
  echo "<div class=info>Sent when a new participant registers for the survey.</div>";
  echo "<textarea class='welcome' name=welcome $readonly>$welcome</textarea>";
  echo "</div>";

  echo "</div>";
}
