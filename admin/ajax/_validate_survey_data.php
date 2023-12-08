<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

function is_associative_array($x)
{
  if(!is_array($x)) { 
    return false; 
  }
  foreach( array_keys($x) as $key ) 
  {
    if(!is_string($key)) {
      return false; 
    }
  }
  return true;

}

function add_warning($findings,$warning) {
  $findings['warnings'][] = $warning;
  return $findings;
}

function add_error($findings,$error) {
  $findings['errors'][] = $error;
  return $findings;
}

function validate_survey_data($json_data)
{
  $findings = [];

  $data = json_decode($json_data,true);
  if(is_null($data)) {
    return add_error($findings,json_last_error_msg());
  } 

  log_dev("Validating survey data: ".print_r($data,true));

  if(!is_associative_array($data)) {
    return add_error($findings,"survey data must be an associative array");
  }

  foreach($data as $key=>$value)
  {
    log_dev("Validating $key data");
    switch($key) {
    case "surveys":   
      $findings = validate_surveys($value,$findings);   
      break;
    case "userids":   
      $findings = validate_userids($value,$findings);   
      break;
    case "responses": 
      $findings = validate_findingss($value,$findings); 
      break;
    default:
      $findings = add_warning($findings,"Invalid survey data key $key");
      break;
    }
  }

  return $findings;
}

function validate_surveys($surveys,$findings)
{
  foreach ($surveys as $name=>$data) {
    $error = null;
    if(!valid_survey_name($name,$error)) {
      $findings = add_error($findings,$error);
      continue;
    }
  }
  return $findings;
}

function valid_survey_name($name,&$error=null)
{
  log_dev("validation survey name: $name");
  if(strlen($name) < 4) {
    $error = "survey name '$name' is shorter than 4 characters";
    return false;
  }
  $m = null;
  if(preg_match("/[^a-zA-Z0-9., -]/",$name,$m)) {
    $error = "survey name '$name' contains invalid '$m[0]' character";
    return false;
  }
  return true;
}

function validate_userids($data,$findings)
{
  return $findings;
}

function validate_findingss($data,$findings)
{
  return $findings;
}

