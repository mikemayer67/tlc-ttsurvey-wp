<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

$pid = $_POST['pid'] ?? null;
if(reopen_survey($pid)) {
  wp_send_json_success();
} else {
  wp_send_json_failure();
}
wp_die();

