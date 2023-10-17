<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');

$response = array('ok'=>true,);

log_dev("validate_content_form POST: ".print_r($_POST,true));

// validate the survey itelf:
//   for now, simply valid yaml... eventually recognized survey structure
$survey = $_POST['survey'] ?? null;
if(!$survey) {
  $response['ok'] = false;
  $response['error'] = "Required";
} else {
  $error = null;
  $parsed = parse_survey_yaml($survey,$error);
  if($parsed) {
    log_dev("parsed: ".print_r($parsed,true));
  } else {
    log_dev("invalid yaml");
    $response['ok'] = false;
    $response['error'] = $error;
  }
}

$rval = json_encode($response);
log_dev(print_r($rval,true));

echo($rval);
wp_die();


