<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if(!plugin_admin_can('data')) {
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/const.php');
require_once plugin_path('admin/content_lock.php');

add_noscript_body();
add_script_body();

function add_noscript_body()
{
  echo "<noscript class='warning'>";
  echo "<p>Managing survey data requires that Javascript be enabled</p>";
  echo "</noscript>";
}

function add_script_body()
{
  echo "<div class='content requires-javascript'>";

  // check to see if we have lock
  $lock = obtain_content_lock();
  if($lock['has_lock']) {
    // we have the lock
    add_data_form();
  } else {
    // someone else has lock
    add_content_lock($lock);
  }
  echo "</div>";
}

function add_data_form()
{
  echo "<div class='data'>";
  add_data_dump();
  add_data_load();
  echo "</div>";

  enqueue_data_javascript();
}

function add_data_dump()
{
  $nonce = esc_attr(wp_create_nonce(DATA_NONCE));
  $href = plugin_url('admin/data_dump.php') . "?nonce=$nonce";

  $timestamp = date('YmdHis');
  $dumpfile = "TimeAndTalentSurvey_data_$timestamp.tlctt";

  echo "<form class='data dump'>";
  echo "<div class='label'>Dump Survey Data</div>";
  echo "<div class='form-body'>";
  echo "<div class='info'>";
  echo "  Captures all user profile, survey content, and response data.<br>";
  echo "  Note that this does <b>not</b> preserve plugin settings.";
  echo "</div>";
  echo "<div class='link-buttons'>";
  echo "<div><a class='data' href='$href&pp=1' target='_blank'>View data dump in new window</a></div>";
  echo "<div><a class='data' href='$href' download='$dumpfile'>Download data dump</a></div>";
  echo "</div>";
  echo "</div>"; // form-body
  echo "</form>";
}

function add_data_load()
{
  echo "<form class='data upload'>";
  wp_nonce_field(DATA_NONCE);
  echo "<div class='label'>Load Survey Data</div>";
  echo "<div class='form-body'>";
  echo "<div class='warning'>";
  echo "Loaing new data will replace all current user profile, survey content, and response data.<br>";
  echo "The survey content revision history will be cleared.</div>";
  echo "<div class='info'>";
  echo "The uploaded data must be JSON formatted.</br>";
  echo "The key/value pairs must be consistent with that dumped using the links above.<br>";
  echo "</div>";

  echo "<div class='header-box'>";
  echo "<input type='file' id='data-file-input' accept='.tlctt'>";
  echo "<a id='data-load'>Load data from file</a>";
  echo "<span id='data-file-name'></span>";
  echo "</div>";

  echo "<div class='data-box'>";
  echo "<textarea id='json-data' name='json_data' rows=20 placeholder='New Survey Data' readonly>";
  echo "</textarea>";
  echo "<div id='validation-status'>validating</div>";
  echo "</div>";

  echo "<div class='validation warnings'>";
  echo "<div class='label'>Warnings</div>";
  echo "<ul><li>warning 1</li><li>warning 2</li></ul>";
  echo "</div>";
  echo "<div class='validation errors'>";
  echo "<div class='label'>Errors</div>";
  echo "<ul><li>error 1</li><li>error 2</li></ul>";
  echo "</div>";

  echo "<div class='button-box'>";
  echo "<div class='ack-box'>";
  echo "<div id='acknowledge-upload'>";
  echo "<input type='checkbox' id='ack-upload-cb'>";
  echo "<label for'ack-upload-cb'>I acknowledge that this will reset <b>all</b> data for <b>all</b> surveys</label>";
  echo "</div><div id='acknowledge-warnings'>";
  echo "<input type='checkbox' id='ack-warnings-cb'>";
  echo "<label for'ack-warnings-cb'>I acknowledge there are potential issues with the survey data</label>";
  echo "</div></div>";
  echo "<input type='button' id='data-upload' class='button button-primary' value='Upload Survey Data' disabled>";
  echo "</div>";

  echo "</div>"; // form-body
  echo "</form>";
}

function enqueue_data_javascript()
{
  $overview_url = implode('?', array(
    parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),
    http_build_query( array(
      'page'=>SETTINGS_PAGE_SLUG,
      'tab'=>'overview',
      'status'=>'Data Uploaded',
    ))
  ));

  wp_register_script(
    'tlc_ttsurvey_data_form',
    plugin_url('admin/js/data_form.js'),
    array('jquery'),
    '1.0.3',
    true
  );
  wp_localize_script(
    'tlc_ttsurvey_data_form',
    'form_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('data_form',wp_create_nonce('data_form')),
      'overview' => $overview_url,
    ),
  );
  wp_enqueue_script('tlc_ttsurvey_data_form');
}


