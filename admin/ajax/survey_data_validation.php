<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/const.php');
require_once plugin_path('include/validation.php');

const VALIDATION_SCHEMA = [
  'surveys'=>[ 'type'=>'hashlist', 'required'=>true, 'rule'=>[
      'name'=>    [ 'type'=>'string',  'required'=>true, 'rule'=>'survey_name'],
      'post_id'=> [ 'type'=>'integer', 'required'=>true, 'rule'=>'positive' ],
      'status'=>  [ 'type'=>'enum',    'required'=>true, 'rule'=>['draft','active','closed'] ],
      'responses'=>[ 'type'=>'integer', 'required'=>false,'rule'=>'natural' ],
      'content'=> [ 'type'=>'hash',    'required'=>false, 'rule'=>[
          'survey'=>  [ 'type'=>'string', 'required'=>false, 'rule'=>'survey_form' ],
          'sendmail'=>[ 'type'=>'hash',   'required'=>false, 'rule'=>[
              'welcome'=> [ 'type'=>'string', 'required'=>false, 'rule'=>'markdown' ],
              'recovery'=>[ 'type'=>'string', 'required'=>false, 'rule'=>'markdown' ]
            ]
          ]
        ]
      ]
    ]
  ],
  'userids'=>[ 'type'=>'hashlist', 'required'=>true, 'rule'=> [
      'userid'=> [ 'type'=>'string',  'required'=>true, 'rule'=>'userid' ],
      'post_id'=>[ 'type'=>'integer', 'required'=>true, 'rule'=>'positive' ],
      'anonid'=> [ 'type'=>'integer', 'required'=>false, 'rule'=>'positive' ],
      'content'=>[ 'type'=>'hash',    'required'=>true, 'rule'=> [
          'pw_hash'=>     [ 'type'=>'string', 'required'=>true,  'rule'=>'pw_hash'  ],
          'fullname'=>    [ 'type'=>'string', 'required'=>true,  'rule'=>'fullname' ],
          'email'=>       [ 'type'=>'string', 'required'=>false, 'rule'=>'email'    ],
          'access_token'=>[ 'type'=>'string', 'required'=>false, 'rule'=>'token'    ]
        ]
      ]
    ]
  ],
  'responses'=>[ 'type'=>'hashlist', 'required'=>false, 'rule'=> [
      'post_id'=>[ 'type'=>'integer', 'required'=>true ]
    ]
  ]
];


function is_hash($x)
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


function validate_survey_data($json_data)
{
  $data = json_decode($json_data,true);
  if(is_null($data)) {
    return add_error([],json_last_error_msg());
  } 

  $findings = validate_hash($data,VALIDATION_SCHEMA,true,'',[]);
  return $findings;
}

function validate_hash($hash,$schema,$required,$scope,$findings)
{
  if(!is_hash($hash)) {
    return add_error($findings,"$scope is not an associative array");
  }

  foreach( $schema as $key=>$element ) {
    if(array_key_exists($key,$hash)) {
      $findings = call_user_func(
        ns('validate_' . $element['type']),
        $hash[$key],
        $element['rule'] ?? null,
        $element['required'] ?? true,
        $scope ? "$scope.$key" : $key,
        $findings
      );
    } else if($element['required']) {
      $findings = add_error($findings,"$scope is missing $key");
    }
  }
  foreach( $hash as $key=>$value ) {
    if(!array_key_exists($key,$schema)) {
      $findings = add_warning($findings,"$scope has unrecognized key: $key");
    }
  }
  return $findings;
}

function validate_hashlist($list,$schema,$required,$scope,$findings)
{
  if(!is_array($list)) {
    return add_error($findings,"$scope is not a list");
  }
  foreach ($list as $index=>$hash) {
    $findings = validate_hash($hash,$schema,$required,"$scope.$index",$findings);
  }
  return $findings;
}

function validate_string($value,$rule,$required,$scope,$findings)
{
  if(empty($value)) {
    if($required) {
      return add_error($findings,"$scope is empty");
    } else {
      return add_warning($findings,"$scope is empty");
    }
  }

  if(!(is_string($value)||is_integer($value))) {
    return add_error($findings,"$scope is not a string");
  }
  $error = "";
  switch($rule) {
  case 'survey_name':
    if(strlen($value) < 4) {
      return add_error($findings,"survey name '$value' is shorter than 4 characters");
    }
    $m = null;
    if(preg_match("/[^a-zA-Z0-9., -]/",$value,$m)) {
      return add_error($findings,"survey name '$value' contains invalid '$m[0]' character");
    }
    break;
  case 'markdown':
    # Anything is ok... The field will be sanitized before being added to database.
    break;
  case 'pw_hash':
    if(!preg_match("/^[\.\$\/ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789]{40,}$/",$value)) {
      return add_error($findings,"$scope: invalid bcrypt");
    }
    break;
  case 'userid':
  case 'fullname':
  case 'email':
    if(!validate_user_input($rule,$value,$error)) {
      return add_error($findings,"$scope: $error");
    }
    break;
  case 'token':
    if(!preg_match("/^[0-9A-Z]{25}$/",$value)) {
      return add_error($findings,"$scope: invalid format");
    }
    break;
  }
  return $findings;
}

function validate_integer($value,$rule,$required,$scope,$findings)
{
  if(is_string($value)) {
    $value = trim($value);
    if(strlen($value) == 0) {
      if($required) {
        return add_error($findings,"$scope is empty");
      } else {
        return add_warning($findings,"$scope is empty");
      }
    }
    if(preg_match('/^-?\d+$/',$value)) {
      $value = intval($value);
    }
  }

  if(!is_integer($value)) {
    return add_error($findings,"$scope is not an integer");
  }

  switch($rule) {
  case "positive":
    if($value < 1) {
      return add_error($findings,"$scope is not a positive value");
    }
    break;
  case "natural":
    if($value < 0) {
      return add_error($findings,"$scope is a negative value");
    }
    break;
  }

  return $findings;
}

function validate_enum($value,$values,$required,$scope,$findings)
{
  if(empty($value)) {
    if($required) {
      return add_error($findings,"$scope is empty");
    } else {
      return add_warning($findings,"$scope is empty");
    }
  }

  if(!in_array($value,$values)) {
    return add_error($findings,"$scope has invalid value: $value");
  }
  return $findings;
}
