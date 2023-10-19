<?php
namespace TLC\TTSurvey;

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if(!plugin_admin_can('manage')) {
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');

add_settings_form();

function add_settings_form()
{
  $action = implode('?', array(
    parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),
    http_build_query( array(
      'page'=>SETTINGS_PAGE_SLUG,
      'tab'=>'overview',
    ))
  ));

  echo "<div class='settings'>";
  echo "<form action='$action' method='POST'>";
  echo "  <input type='hidden' name='action' value='update'>";

  wp_nonce_field(OPTIONS_NONCE);
  add_settings_status();
  add_admin_settings();
  add_pdf_uri_setting();
  add_log_level_setting();

  $class = 'submit button button-primary button-large';
  echo "  <input type='submit' class='$class' value='Save'>";
  echo "</form></div>";
}

function add_settings_status()
{
  $current_status = current_survey()['status'] ?? null;

  echo "<div class='label'>Survey Status</div>";
  if($current_status == SURVEY_IS_DRAFT) {
    echo "<div class='info'>";
    echo "  Changing the status from Draft to Active will open the survey.";
    echo "</div>";
    echo "<div class='info'>";
    echo "  Once active, changing back to Draft may invalidate any responses ";
    echo "  already received.";
    echo "</div>";
    echo "<div class='settings'>";
    echo "  <span class='input-label'>Status</span>";
    echo "  <select name='survey_status'>";
    echo "    <option value='".SURVEY_IS_DRAFT."' selected>Draft</option>";
    echo "    <option value='".SURVEY_IS_ACTIVE."'>Active</option>";
    echo "  </select>";
    echo "</div>";
  } elseif($current_status == SURVEY_IS_ACTIVE) {
    echo "<div class='info'>";
    echo "  Changing the status from Active to Closed will close the survey.";
    echo "</div>";
    echo "<div class='warning'>";
    echo "  Changing the status back to Draft may result in corruption of responses";
    echo "  already received.";
    echo "</div>";
    echo "<div class='settings'>";
    echo "  <span class='input-label'>Status</span>";
    echo "  <select name='survey_status'>";
    echo "    <option value='".SURVEY_IS_DRAFT."'>Draft</option>";
    echo "    <option value='".SURVEY_IS_ACTIVE."' selected>Active</option>";
    echo "    <option value='".SURVEY_IS_CLOSED."'>Closed</option>";
    echo "  </select>";
    echo "</div>";
  } else {
    echo "<div class='info'>";
    echo "  There is no active or draft survey. Create/reopen one in the Content tab.";
    echo "</div>";
  }
}

function add_admin_settings()
{
  echo "<div class='label'>Survey Admins</div>";
  echo "<table class='caps'>";
  echo "<tr><th></th><th>Manage</th><th>Content</th><th>Responses</th></tr>";

  $caps = survey_capabilities();
  foreach(get_users() as $user) {
    $id = $user->id;
    $name = $user->display_name;
    $manage = $caps['manage'][$id] ? "checked" : "";
    $content = $caps['content'][$id] ? "checked" : "";
    $response = $caps['responses'][$id] ? "checked" : "";
    $hidden_manage = '';

    if(user_can($id,'manage_options')) {
      $manage = 'checked disabled';
      $hidden_manage = "<input type='hidden' value=1 name='caps[manage][$id]'>";
    }

    echo "<tr>";
    echo "<td class='name'>$name</td>";
    echo "<td><div>";
    echo "  <input type='checkbox' value=1 name='caps[manage][$id]' $manage>";
    echo $hiden_manage;
    echo "</div></td><td><div>";
    echo "  <input type='checkbox' value=1 name='caps[content][$id]' $content>";
    echo "</div></td><td><div>";
    echo "  <input type='checkbox' value=1 name='caps[responses][$id]' $response>";
    echo "</div></td>";
    echo "</tr>";

  }
  echo "</table>";
}

function add_pdf_uri_setting()
{
  $pdf_uri = survey_pdf_uri();
  $pattern = '^(http|https|ftp|ftps)://[a-zA-Z].*$';
  echo "<div class='label'>Survey Download URL</div>";
  echo "<div class='info'>Location for a downloadable copy of the survey</div>";
  echo "<div class='settings'>";
  echo "<input type='URL' size=50 name='pdf_uri' value='$pdf_uri' pattern='$pattern'>";
  echo "</div>";
}

function add_log_level_setting()
{
  $cur_level = survey_log_level();
  echo "<div class='label'>Log Level</div>";
  echo "<div class='settings'>";
  echo "<select name='log_level'>";
  foreach(array("DEV","INFO","WARNING","ERROR") as $log_level) {
    $selected = ($log_level == $cur_level) ? "selected" : "";
    echo "<option value='$log_level' $selected>$log_level</option>";
  }
  echo "</select></div>";
}
