<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

$name = $_POST['name'] ?? null;
if(create_new_survey($name)) {
  wp_send_json_success();
} else {
  wp_send_json_error();
}
wp_die();


