<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

function decode_json_data($data,&$error=null) 
{
  $data = $_POST['survey_data'];
  $data = stripslashes_deep($data);
  $data = json_decode(stripslashes_deep($data,true));
  if(is_null($data)) {
    $error = json_last_error_msg();
  }
  return $data;
}

function validate_suvey_data(,$data,&$error=null)
{
  if(!validate_survey_data($data,$error)) {
    return array('ok'=>false, 'warning'=>$error);
  }
  if(!validate_userid_data($data,$error)) {
    return array('ok'=>false, 'warning'=>$error);
  }
  if(!validate_responses_data($data,$error)) {
    return array('ok'=>false, 'warning'=>$error);
  }

  return array('ok'=>true);
}

function validate_survey_data($data,&$error=null)
{
  return true;
}

function validate_userid_data($data,&$error=null)
{
  return true;
}

function validate_response_data($data,&$error=null)
{
  return true;
}

function upload_survey_data($data,&$error=null)
{
  return true;
}

$do_upload = $_POST['upload'] ?? false;
$data = $_POST['survey_data'] ?? '';

$data = stripslashes_deep($data);
$data = json_decode($data,true);
if(is_null($data)) {
  $error = json_last_error_msg();
  $result = array('ok'=>false, 'error'=>$error);
  echo json_encode($result);
  wp_die();
}

$error = '';
$result = validate_survey_data($data,$error);
if($result && $do_upload) {
  $result = upload_survey_data($data,$error);
}

if($result) {
  $response = ('ok'=>true);
} else {
  $response = ('ok'=>false, 'warning'=>$error);
}

echo json_encode($response);
wp_die();
