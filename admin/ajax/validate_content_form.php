<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

// validate the survey itelf:
//   for now, simply valid yaml... eventually recognized survey structure
$survey = $_POST['survey'] ?? null;
if(!$survey) {
  wp_send_json_error("Required");
  wp_die();
}

$error = null;
if(!parse_survey_yaml($survey,$error)) {
  wp_send_json_error($error);
  wp_die();
}

wp_send_json_success();
wp_die();


