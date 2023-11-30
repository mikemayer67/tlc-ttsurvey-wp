<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

function validate_survey_data($data)
{
  validate_surveys($data);
  validate_userids($data);
  validate_responses($data);
}

function validate_surveys($data)
{
  // on failure:
  // wp_send_json_failure($error)
  // wp_die()
}

function validate_userids($data)
{
}

function validate_resposes$data)
{
}

function upload_survey_data($data)
{
}

log_dev("upload_survey_data POST: ".print_r($_POST,true));

$scope = $_POST['scope'] ?? '';
$data = $_POST['survey_data'];

$data = stripslashes_deep($data);
$data = json_decode($data,true);
if(is_null($data)) {
  wp_send_json_failure(json_last_error_msg());
  wp_die();
}

validate_survey_data($data);
if($scope == 'upload') {
  upload_survey_data($data);
}

wp_send_json_success();
wp_die();
