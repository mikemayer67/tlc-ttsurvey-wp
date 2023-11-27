<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if(!plugin_admin_can('data')) {
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

add_data_form();

function add_data_form()
{
  echo "<div class='data'>";
  echo "<h2>Data Dump</h2>";
  echo "<div class='info'>Captures all user profile, survey content, and response data.</div>";
  echo "<div class='info'>Note that this does <b>not</b> preserve plugin settings.</div>";
  echo "<div class='dumps'>";
  echo "<div><a class='data' data-action='view'>View JSON data in new window</a></div>";
  echo "<div><a class='data' data-action='download'>Download JSON data</a></div>";
  echo "</div>";
  echo "<h2>Upload Data</h2>";
  echo "<div class='info'>Coming with Issue #109</div>";
  echo "</div>";

  enqueue_data_javascript();
}


function enqueue_data_javascript()
{
  wp_register_script(
    'tlc_ttsurvey_data_actions',
    plugin_url('admin/js/data_actions.js'),
    array('jquery'),
    '1.0.3',
    true
  );
  wp_localize_script(
    'tlc_ttsurvey_data_actions',
    'data_vars',
    array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => array('data_actions',wp_create_nonce('data_actions')),
    ),
  );
  wp_enqueue_script('tlc_ttsurvey_data_actions');
}
