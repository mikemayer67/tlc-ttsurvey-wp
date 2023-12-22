<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/const.php');
require_once plugin_path('include/validation.php');

const VALIDATION_SCHEMA = [ 'type'=>'hash', 'required'=>true, 'keys'=>[
  'surveys' => [ 'type'=>'hashlist', 'required'=>true, 'keys'=>[
    'name' =>     [ 'type'=>'string',  'required' => true, 'validate'=>'survey_name'    ],
    'post_id' =>  [ 'type'=>'integer', 'required'=>true                                 ],
    'status' =>   [ 'type'=>'enum',    'required'=>true, 'values'=>SURVEY_STATUS_VALUES ],
    'response' => [ 'type'=>'integer', 'required'=>false                                ],
    'content' =>  [ 'type'=>'hash',    'required'=>false, 'keys'=>[
      'survey' =>   [ 'type'=>'string',  'required'=>false, 'validate'=>'survey_form' ],
      'sendmail' => [ 'type'=>'hash',    'required'=>false, 'keys'=>[
        'welcome' =>  [ 'type'=>'string',  'required'=>false, 'validate'=>'markdown' ],
        'recovery' => [ 'type'=>'string',  'required'=>false, 'validate'=>'markdown' ],
      ]],
    ]],
  ]],
  'userids' => [ 'type'=>'hashlist',  'required'=>true, 'keys'=>[
    'userid' =>  [ 'type'=>'string',  'required'=>true, 'validate'=>'userid' ],
    'post_id' => [ 'type'=>'integer', 'required'=>true                       ],
    'anonid' =>  [ 'type'=>'string',  'required'=>true, 'validate'=>'anonid' ],
    'content' => [ 'type'=>'hash',    'required'=>true, 'keys'=>[
      'pw_hash' =>      [ 'type'=>'string', 'required'=>true,  'validate'=>'pw_hash'  ],
      'fullname' =>     [ 'type'=>'string', 'required'=>true,  'validate'=>'fullname' ],
      'access_token' => [ 'type'=>'string', 'required'=>false, 'validate'=>'token'    ],
    ]],
  ]],
  'responses' => [ 'type'=>'hashlist', 'required'=>false, 'keys'=>[
  ]],
]];

// Support functions

function is_associative_array($x)
{
  if(!is_array($x)) { return false; }
  foreach( array_keys($x) as $key ) {
    if(!(is_string($key)||is_integer($key))) { return false; }
  }
  return true;
}

function add_warning($findings,$warning) 
{
  $findings['warnings'][] = $warning;
  return $findings;
}

function add_error($findings,$error) 
{
  $findings['errors'][] = $error;
  return $findings;
}

// General entry point for all survey data (surveys, userids, response) in the database

function validate_survey_data($json_data)
{
  $findings = [];

  $data = json_decode($json_data,true);
  if(is_null($data)) {
    return add_error($findings,json_last_error_msg());
  } 

  if(!is_associative_array($data)) {
    return add_error($findings,"survey data is not an associative array");
  }

  foreach(['surveys','userids','responses'] as $key) {
    if(!array_key_exists($key,$data)) {
      $findings = add_warning($findings,"Contains no $key");
    }
  }

  foreach($data as $key=>$value)
  {
    switch($key) {
    case "surveys":   
      $findings = validate_surveys($value,$findings);   
      break;
    case "userids":   
      $findings = validate_userids($value,$findings);   
      break;
    case "responses": 
      $findings = validate_responses($value,$findings); 
      break;
    default:
      $findings = add_warning($findings,"Invalid survey data key $key");
      break;
    }
  }

  return $findings;
}

// Validation of survey content

function validate_surveys($surveys,$findings)
{
  log_dev("surveys: ".print_r($surveys,true));
  if(!is_associative_array($surveys)) {
    return add_error($findings,"surveys is not an associative array");
  }

  $post_ids = array();
  foreach ($surveys as $name=>$data) {
    $error = null;
    if(!valid_survey_name($name,$error)) {
      $findings = add_error($findings,$error);
      continue;
    }
    if(!is_associative_array($data)) {
      $findings = add_error($findings,"$name survery data is not an associative array");
      continue;
    }
    if(!array_key_exists("post_id",$data)) {
      $findings = add_error($findings,"$name survey is missing post_id");
    } else {
      $post_id = $data['post_id'];
      if(in_array($post_id,$post_ids)) {
        $findings = add_error($findings,"Multiple surveys associated with post_id $post_id");
      } else {
        $post_ids[] = $post_id;
      }
    }

    $findings = validate_survey_content($name,$data,$findings);
    $findings = validate_survey_status($name,$data,$findings);
  }

  return $findings;
}

function valid_survey_name($name,&$error=null)
{
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

function validate_survey_content($name,$survey,$findings)
{
  if(!array_key_exists("content",$survey)) {
    $findings = add_warning($findings,"$name survey is missing content");
  }
  $content = $survey['content'];
  if(!is_associative_array($content)) {
    return add_error($findings,"$name survey content is not an associative array");
  }
  if(!array_key_exists("survey",$content)) {
    $findings = add_warning($findings,"$name survey contains no content data");
  }
  foreach($content as $key=>$value) {
    switch($key) {
    case 'survey':
      $findings = validate_content_survey($findings,$name,$value);
      break;
    case 'sendmail':
      $findings = validate_content_sendmail($findings,$name,$value);
      break;
    default:
      $findings = add_warning($findings,"$name survey content contains invalid $key key");
      break;
    }
  }

  return $findings;
}

function validate_content_survey($findings,$name,$survey)
{
  // @@@ TODO Fill this out once survey data structure has been established
  return $findings;
}

function validate_content_sendmail($findings,$name,$sendmail)
{
  if(!is_associative_array($sendmail)) {
    return add_error($findings,"$name survey sendmail content is not an associative array");
  }
  $sendmail_keys = array_keys(SENDMAIL_TEMPLATES);
  foreach($sendmail as $key=>$value) {
    if(!in_array($key,$sendmail_keys)) {
      $findings = add_warning($findings,"$name survey contains invalid sendmail key ($key)");
    } else if(!is_string($value)) {
      $findings = add_error($findings,"$name survey $key sendmail customization value is not a string");
    }
  }
  return $findings;
}

function validate_survey_status($name,$survey,$findings)
{
  if(!array_key_exists("status",$survey)) {
    return add_error($findings,"$name survey is missing status");
  }
  $status = strtolower($survey['status']);
  if(!in_array($status,SURVEY_STATUS_VALUES)) {
    return add_error($findings,"$name survey has invalid status: $status");
  }
  return $findings;
}

// Validation of user data

function validate_userids($userids,$findings)
{
  if(!is_associative_array($userids)) {
    return add_error($findings,"userids is not an associative array");
  }
  foreach( $userids as $userid=>$data ) {
    $error = "";
    if(!validate_user_input("userid",$userid,$error)) {
      $findings = add_error($findings,"Invalid userid: $userid ($error)");
    } else {
      $findings = validate_userid_and_data($findings, $userid, $data);
    }
  }
  return $findings;
}

function validate_userid_and_data($findings, $userid, $data)
{
  if(!is_associative_array($data)) {
    return add_error($findings,"User data for $userid is not an associative array");
  }
  return $findings;
}

// Validation of user responses

function validate_responses($data,$findings)
{
  return $findings;
}
