<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

$name = $_POST['name'] ?? null;
$parent_id = $_POST['parent_id'] ?? 0;

if(create_new_survey($name,$parent_id)) {
  wp_send_json_success();
} else {
  wp_send_json_error();
}
wp_die();


