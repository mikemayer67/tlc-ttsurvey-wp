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
  $href = plugin_url('admin/data_view.php') . "?nonce=$nonce";

  $timestamp = date('YmdHis');
  $dumpfile = "TimeAndTalentSurvey_data_$timestamp.json";
  $nonce = esc_attr(wp_create_nonce(DATA_NONCE));

  echo "<form class='data dump'>";
  echo "<div class='label'>Dump Survey Data</div>";
  echo "<div class='info'>";
  echo "  Captures all user profile, survey content, and response data.";
  echo "</div><div class='info'>";
  echo "  Note that this does <b>not</b> preserve plugin settings.";
  echo "</div>";
  echo "<div class='link-buttons'>";
  echo "<div><a class='data' href='$href' target='_blank'>View JSON data in new window</a></div>";
  echo "<div><a class='data' href='$href' download='$dumpfile'>Download JSON data</a></div>";
  echo "</div>";
  echo "</form>";
}

function add_data_load()
{
  echo "<form class='data upload'>";
  wp_nonce_field(DATA_NONCE);
  echo "<div class='label'>Load Survey Data</div>";
  echo "<div class='warning'>";
  echo "Loaing new data will <b>replace</b> all current user profile, survey content, and response data.";
  echo "</div><div class='warning'>";
  echo "The survey content revision history will be cleared.</div>";
  echo "<div class='info'>";
  echo "The uploaded data must be JSON formatted with the following structure:";
  echo "</div>";

  echo "<div class='upload-file'>";
  echo "<label>Upload JSON: </label>";
  echo "<input type='file' id='upload-file' name='upload_file'>";
  echo "</div>";

  echo "<textarea id='new-data' name='json_data' rows=20 placeholder='New Survey Data'>";
  echo "</textarea>";

  echo "</form>";
}

function enqueue_data_javascript()
{
  $overview_url = implode('?', array(
    parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),
    http_build_query( array(
      'page'=>SETTINGS_PAGE_SLUG,
      'tab'=>'overview',
      'status'=>'data uploaded',
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


