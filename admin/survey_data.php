<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if(!plugin_admin_can('data')) {
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/const.php');

add_data_form();

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
  $dumpfile = "TimeAndTalentSurvey_data_$timestamp.json";

  echo "<form class='data dump'>";
  echo "<div class='label'>Dump Survey Data</div>";
  echo "<div class='form-body'>";
  echo "<div class='info'>";
  echo "  Captures all user profile, survey content, and response data.<br>";
  echo "  Note that this does <b>not</b> preserve plugin settings.";
  echo "</div>";
  echo "<div class='link-buttons'>";
  echo "<div><a class='data' href='$href&pp=1' target='_blank'>View JSON data in new window</a></div>";
  echo "<div><a class='data' href='$href' download='$dumpfile'>Download JSON data</a></div>";
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
  echo "<input type='file' id='json-data-file'>";
  echo "<a id='data-load'>Load data from file</a>";
  echo "<span id='data-status'>Info</span>";
  echo "</div>";

  echo "<textarea id='json-data' name='json_data' rows=20 placeholder='New Survey Data'>";
  echo "</textarea>";
  echo "<div id='validation-status'></div>";

  echo "<div class='button-box'>";
  echo "<input type='checkbox' id='confirm-upload'>";
  echo "<label for'confirm-upload'>I realize this will overwrite all existing survey data</label>";
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


