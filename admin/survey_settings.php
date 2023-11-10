<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if(!plugin_admin_can('manage')) {
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/const.php');
require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');

add_settings_form();

function add_settings_form()
{
  echo "<div class='settings'>";
  echo "<form class='settings'>";

  wp_nonce_field(OPTIONS_NONCE);
  add_settings_status();
  add_admin_settings();
  add_pdf_uri_setting();
  add_log_settings();
  add_post_editor();

  echo "<div class='button-box'>";
  echo "<input type='submit' class= 'submit button button-primary button-large' value='Save'>";
  echo "</div>";

  echo "</form></div>";

  enqueue_settings_javascript();
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
  echo "<div class='label'>Survey Admins<span class='admin-error'></span></div>";
  echo "<table class='caps'>";
  echo "<tr><th></th>";
  foreach(explode(' ','Primary Manage Content Response Tech') as $role) {
    echo "<th>$role</th>";
  }
  echo "</tr>";

  $caps = survey_capabilities();
  $primary_admin = survey_primary_admin();
  foreach(get_users() as $user) {
    $id = $user->ID;
    $name = $user->display_name;
    $primary  = ( $id == $primary_admin            ) ? "checked" : "";
    $manage   = ( $caps['manage'][$id]    ?? false ) ? "checked" : "";
    $content  = ( $caps['content'][$id]   ?? false ) ? "checked" : "";
    $response = ( $caps['responses'][$id] ?? false ) ? "checked" : "";
    $tech     = ( $caps['tech'][$id]      ?? false ) ? "checked" : "";
    $hidden_manage = '';

    echo "<tr>";
    echo "<td class='name'>$name</td>";
    echo "<td><div>";
    echo "<input class='primary' type='radio' name='primary_admin' value='$id' $primary>";
    echo "</div></td><td><div>";
    if(user_can($id,'manage_options')) {
      echo "<input type='checkbox' checked disabled>";
      echo "<input class='manage $id' type='hidden' value=1 name='caps[manage][$id]'>";
    } else {
      echo "<input class='manage $id' type='checkbox' value=1 name='caps[manage][$id]' $manage>";
    }
    echo "</div></td><td><div>";
    echo "<input type='checkbox' value=1 name='caps[content][$id]' $content>";
    echo "</div></td><td><div>";
    echo "<input type='checkbox' value=1 name='caps[responses][$id]' $response>";
    echo "</div></td><td><div>";
    echo "<input type='checkbox' value=1 name='caps[tech][$id]' $tech>";
    echo "</div></td>";
    echo "</tr>";

  }
  echo "</table>";
}

function add_pdf_uri_setting()
{
  $pdf_uri = survey_pdf_uri();
  $pattern = '^(http|https|ftp|ftps)://[a-zA-Z].*$';
  echo "<div class='label'>Download URL</div>";
  echo "<div class='info'>Location for a downloadable copy of the survey</div>";
  echo "<div class='settings'>";
  echo "<input type='URL' size=50 name='pdf_uri' value='$pdf_uri' pattern='$pattern'>";
  echo "</div>";
}

function add_log_settings()
{
  echo "<div class='label'>Logging</div>";
  echo "<table class='settings'>";

  $cur_level = survey_log_level();
  echo "<tr>";
  echo "<td class='input-label'>Log Level</td>";
  echo "<td class='input-value'><select name='log_level'>";
  foreach(LOGGER_ as $log_level => $label) {
    $selected = ($log_level == $cur_level) ? "selected" : "";
    echo "<option value='$log_level' $selected>$label</option>";
  }
  echo "</select></td></tr>";

  echo "<tr><td></td>";
  echo "<td class='input-value'>";
  $log_href = plugin_url(PLUGIN_LOG_FILE);
  $timestamp = date('YmdHis');
  $logfile = "TimeAndTalentSurvey_$timestamp.log";
  echo "<a class='log-file' href='$log_href' target='_blank'>View Log File</a>";
  echo "<br>";
  echo "<a class='log-file' href='$log_href' download='$logfile'>Download Log File</a>";
  echo "</td></tr>";

  echo "<tr><td></td>";
  echo "<td class='input-value'>";
  echo "<button class='clear-log'>Clear Log</button>";
  echo "</td></tr>";

  echo "</table>";
}

function add_post_editor()
{
  echo "<div class='label'>Survey Post Editor</div>";
  echo "<div class='info'>";
  echo "Enable deletion, renaming, and revision management of surveys in the wp_posts table. ";
  echo "</div>";
  echo "<table class='settings'>";

  $cur_post_ui = survey_post_ui();
  echo "<tr>";
  echo "<td class='input-label'>Survey Post UI</td>";
  echo "<td class='input-value'><select name='survey_post_ui'>";
  foreach(POST_UI_ as $post_ui => $label) {
    $selected = ($post_ui == $cur_post_ui) ? "selected" : "";
    echo "<option value='$post_ui' $selected>$label</option>";
  }
  echo "</select></td></tr>";

  $cur_post_ui = user_post_ui();
  echo "<tr>";
  echo "<td class='input-label'>User Post UI</td>";
  echo "<td class='input-value'><select name='user_post_ui'>";
  foreach(POST_UI_ as $post_ui => $label) {
    $selected = ($post_ui == $cur_post_ui) ? "selected" : "";
    echo "<option value='$post_ui' $selected>$label</option>";
  }
  echo "</select></td></tr>";

  echo "</table>";
}

function enqueue_settings_javascript()
{
  $overview_url = implode('?', array(
    parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),
    http_build_query( array(
      'page'=>SETTINGS_PAGE_SLUG,
      'tab'=>'overview',
      'status'=>'updated',
    ))
  ));

  wp_register_script(
    'tlc_ttsurvey_settings_form',
    plugin_url('admin/js/settings_form.js'),
    array('jquery'),
    '1.0.3',
    true
  );
  wp_localize_script(
    'tlc_ttsurvey_settings_form',
    'form_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('settings_form',wp_create_nonce('settings_form')),
      'overview' => $overview_url,
    ),
  );
  wp_enqueue_script('tlc_ttsurvey_settings_form');
}
