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
  sleep(3);
  // on failure:
  // wp_send_json(array('success'=>false, 'warning'=>$error));
  // wp_die()
}

function validate_userids($data)
{
}

function validate_responses($data)
{
}

function upload_survey_data($data)
{
}

$scope = $_POST['scope'] ?? '';
$data = $_POST['survey_data'];

$data = stripslashes_deep($data);
$data = json_decode($data,true);
if(is_null($data)) {
  wp_send_json(array('success'=>false, 'error'=>json_last_error_msg()));
  wp_die();
}

validate_survey_data($data);
if($scope == 'upload') {
  upload_survey_data($data);
}

wp_send_json_success();
wp_die();
