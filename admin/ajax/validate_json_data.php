<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');


function validate_json_data($json_data)
{
  $json_data = trim($json_data);
  $json_data = stripslashes_deep($json_data);
  if(strlen($json_data) == 0) { 
    return array('ok'=>true); 
  }

  $data = json_decode($json_data,true);
  if(is_null($data)) { 
    return array('ok'=>false, 'error'=>json_last_error_msg()); 
  }

  $valid_keys = array('surveys','userids','responses');
  $required_keys = array('surveys','userids','responses');
  $data_keys = array_keys($data);

  $invalid_keys = array_values(array_diff($data_keys,$valid_keys));
  if($invalid_keys) {
    return array('ok'=>false, 'warning'=>"Invalid data key: $invalid_keys[0]");
  }

  $missing_keys = array_values(array_diff($required_keys,$data_keys));
  log_dev(print_r($missing_keys,true));
  if($missing_keys) {
    return array('ok'=>false, 'warning'=>"Missing required data key: $missing_keys[0]");
  }

  $error = null;
  if(!validate_survey_data($data['surveys'],$error))
  {
    return array('ok'=>false, 'warning'=>$error);
  }

  return array('ok'=>true);
}

$result = validate_json_data($_POST['json_data'] ?? '');
echo json_encode($result);
wp_die();
