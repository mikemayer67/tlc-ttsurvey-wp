<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

if(!plugin_admin_can('data')) {
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

echo "<div class='data'>";
echo "<h2>Data Dump</h2>";
echo "<div class='info'>Captures all user profile, survey content, and response data.</div>";
echo "<div class='info'>Note that this does <b>not</b> preserve plugin settings.</div>";
echo "<div class='dumps'>";
echo "<div><a class='data view'>View JSON data in new window</a></div>";
echo "<div><a class='data download'>Download JSON data</a></div>";
echo "</div>";
echo "<h2>Upload Data</h2>";
echo "<div class='info'>Coming with Issue #109</div>";
echo "</div>";
