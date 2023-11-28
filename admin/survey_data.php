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
  echo "<form class='data'>";

  $nonce = esc_attr(wp_create_nonce(DATA_NONCE));
  add_data_dump($nonce);
  add_data_load($nonce);

  echo "</form></div>";
}

function add_data_dump($nonce)
{
  $href = plugin_url('admin/data_view.php') . "?nonce=$nonce";

  $timestamp = date('YmdHis');
  $dumpfile = "TimeAndTalentSurvey_data_$timestamp.json";

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
}

function add_data_load($nonce)
{
  $href = plugin_url('admin/data_view.php') . "?nonce=$nonce";
  echo "<div class='label'>Load Survey Data</div>";
  echo "<div class='info'>Coming with Issue #109</div>";
}
