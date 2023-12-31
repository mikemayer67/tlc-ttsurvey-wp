<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/const.php');

function adjust_and_validate_user_input($key,&$value,&$error=null)
{
  $value = adjust_user_input($key,$value);
  return validate_user_input($key,$value,$error);
}

function adjust_user_input($key,$value)
{
  $value = trim(stripslashes($value??''));

  switch($key)
  {
  case 'fullname':
    $value = preg_replace('/\'+/',"'",$value);  // condense multiple apostrophes
    $value = preg_replace('/-+/',"-",$value);   // condense multiple hyphens
    $value = preg_replace('/~+/',"~",$value);   // consense multiple tildes
    // fallthrough is intentional
  case 'password':
    $value = preg_replace('/\s+/',' ',$value);  // condense multiple whitespace
    $value = preg_replace('/\s/',' ',$value);   // only use ' ' for whitespace
    break;
  }
  return $value;
}

function validate_user_input($key,$value,&$error=null)
{
  $error = '';

  if($key=='fullname')
  {
    $invalid_end = "'~-";
    $valid = "A-Za-z\x{00C0}-\x{00FF} .'~-";
    if(preg_match("/([^$valid])/",$value,$m)) {
      $error = "names cannot contain $m[1]";
    }
    elseif(preg_match("/\.\S/",$value,$m)) {
      $error = "name cannot contain period";
    }
    elseif(preg_match("/(?:^|\s)([$invalid_end])/",$value,$m)) {
      $error = "names cannot start with $m[1]";
    }
    elseif(preg_match("/([$invalid_end])(?:$|\s)/",$value,$m)) {
      $error = "names cannot end with $m[1]";
    }
    elseif(strlen($value)<2) {
      $error = "no initials, please";
    }
    elseif(!str_contains($value,' ')) {
      $error = "full name, please";
    }
  }
  elseif($key=='userid')
  {
    if(strlen($value)<8)      { $error = "too short"; }
    elseif(strlen($value)>16) { $error = "too long"; }
    elseif(preg_match("/\s/",$value)) {
      $error = "cannot contain spaces";
    }
    elseif(preg_match("/^[^a-zA-Z]/",$value)) {
      $error = "must start with a letter";
    }
    elseif(!preg_match("/^[a-zA-Z][a-zA-Z0-9]+$/",$value)) {
      $error = "letters/numbers only";
    }
  }
  elseif($key=='password')
  {
    if(strlen($value)<8)       { $error = "too short"; }
    elseif(strlen($value)>128) { $error = "too long"; }
    elseif(!preg_match("/[a-zA-Z]/",$value)) {
      $error = "must contain at least one letter";
    }
    elseif(preg_match("/([^a-zA-Z0-9 !@%^*_=~,.-])/",$value,$m)) 
    {
      $error = "cannot contain ($m[1])";
    }
  }
  elseif($key=="email")
  {
    # email is optional, so empty is ok
    if($value)
    {
      $email = filter_var($value,FILTER_VALIDATE_EMAIL);
      if(!$email) { $error = "invalid format"; }
    }
  }

  return strlen($error) == 0;
}

function validate_survey_data($json_data)
{
  $validation = new SurveyDataValidation($json_data);

  $findings = array();
  if($validation->warnings) { $findings['warnings'] = $validation->warnings; }
  if($validation->errors)   { $findings['errors']   = $validation->errors;   }
  return $findings;
}


class SurveyDataValidation
{
  public $warnings = [];
  public $errors = [];

  public function __construct($json_data)
  {
    $data = json_decode($json_data,true);
    if(is_null($data)) {
      $this->error[] = json_last_error_msg();
      return;
    } 

    // Issue 111: @@@ TODO: Add response schema
    // - validate that surveys.responses is consistent with content of responses data

    $survey_data_schema = [
      'surveys'=>[ 'type'=>'hashlist', 'required'=>true, 'schema'=>[
          'name'=>    [ 'type'=>'string',  'required'=>true, 'rule'=>'survey_name'],
          'post_id'=> [ 'type'=>'integer', 'required'=>true, 'rule'=>'positive' ],
          'status'=>  [ 'type'=>'enum',    'required'=>true, 'values'=>['draft','active','closed'] ],
          'responses'=>[ 'type'=>'integer', 'required'=>false,'rule'=>'natural' ],
          'content'=> [ 'type'=>'hash',    'required'=>false, 'schema'=>[
              'survey'=>  [ 'type'=>'string', 'required'=>false, 'rule'=>'survey_form' ],
              'sendmail'=>[ 'type'=>'hash',   'required'=>false, 'schema'=>[
                  'welcome'=> [ 'type'=>'string', 'required'=>false, 'rule'=>'markdown' ],
                  'recovery'=>[ 'type'=>'string', 'required'=>false, 'rule'=>'markdown' ]
                ]
              ]
            ]
          ]
        ]
      ],
      'userids'=>[ 'type'=>'hashlist', 'required'=>true, 'schema'=> [
          'userid'=> [ 'type'=>'string',  'required'=>true, 'rule'=>'userid' ],
          'post_id'=>[ 'type'=>'integer', 'required'=>true, 'rule'=>'positive' ],
          'anonid'=> [ 'type'=>'integer', 'required'=>false, 'rule'=>'positive' ],
          'content'=>[ 'type'=>'hash',    'required'=>true, 'schema'=> [
              'pw_hash'=>     [ 'type'=>'string', 'required'=>true,  'rule'=>'pw_hash'  ],
              'fullname'=>    [ 'type'=>'string', 'required'=>true,  'rule'=>'fullname' ],
              'email'=>       [ 'type'=>'string', 'required'=>false, 'rule'=>'email'    ],
              'access_token'=>[ 'type'=>'string', 'required'=>false, 'rule'=>'token'    ]
            ]
          ]
        ]
      ],
      'responses'=>[ 'type'=>'hashlist', 'required'=>false, 'schema'=> [
          'post_id'=>[ 'type'=>'integer', 'required'=>true ]
        ]
      ]
    ];

    $this->validate_hash($data,$survey_data_schema,true,'');
  }

  private function validate_hash($hash,$schema,$required,$scope)
  {
    // verify that this is a hash
    if(!is_array($hash)) {
      $this->error[] = "$scope is not an associative array";
      return;
    }
    foreach(array_keys($hash) as $key) {
      if(!(is_string($key)||is_integer($key))) {
        $this->error[] = "$scope is not an associative array";
        return;
      }
    }

    foreach( $schema as $key=>$element ) {
      if(array_key_exists($key,$hash)) {
        $value = $hash[$key];
        $required = $element['required'];
        $element_scope = $scope ? "$scope.$key": $key;
        switch($element['type']) {
        case 'string':
          $this->validate_string( $value, $element['rule'], $required, $element_scope);
          break;
        case 'integer':
          $this->validate_integer( $value, $element['rule'], $required, $element_scope);
          break;
        case 'enum':
          $this->validate_enum( $value, $element['values'], $required, $element_scope);
          break;
        case 'hash':
          $this->validate_hash( $value, $element['schema'], $required, $element_scope);
          break;
        case 'hashlist':
          $this->validate_hashlist( $value, $element['schema'], $required, $element_scope);
          break;
        }
      } else if($element['required']) {
        $this->errors[] = "$scope is missing $key";
      }
    }
    foreach( $hash as $key=>$value ) {
      if(!array_key_exists($key,$schema)) {
        $this->warnings[] = "$scope has unrecognized key: $key";
      }
    }
  }

  private function validate_hashlist($list,$schema,$required,$scope)
  {
    if(!is_array($list)) {
      $this->errors[] = "$scope is not a list";
      return;
    }
    foreach ($list as $index=>$hash) {
      $this->validate_hash($hash,$schema,$required,"$scope.$index");
    }
  }

  private function validate_string($value,$rule,$required,$scope)
  {
    if(empty($value)) {
      if($required) { $this->errors[]   = "$scope is empty"; } 
      else          { $this->warnings[] = "$scope is empty"; }
      return;
    }

    if(!(is_string($value)||is_integer($value))) {
      $this->errors[] = "$scope is not a string";
      return;
    }

    switch($rule) 
    {
      case 'survey_name':
        if(strlen($value) < 4) {
          $this->errors[] = "survey name '$value' is shorter than 4 characters";
        } else {
          $m = null;
          if(preg_match("/[^a-zA-Z0-9., -]/",$value,$m)) {
            $this->errors[] = "survey name '$value' contains invalid '$m[0]' character";
          }
        }
        break;

      case 'markdown':
        # Anything is ok... The field will be sanitized before being added to database.
        break;

      case 'pw_hash':
        if(!preg_match("/^[\.\$\/ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789]{40,}$/",$value)) {
          $this->errors[] = "$scope: invalid bcrypt";
        }
        break;

      case 'userid':
      case 'fullname':
      case 'email':
        $error = "";
        if(!validate_user_input($rule,$value,$error)) {
          $this->errors[] = "$scope: $error";
        }
        break;

      case 'token':
        if(!preg_match("/^[0-9A-Z]{25}$/",$value)) {
          $this->errors[] = "$scope: invalid format";
        }
        break;
    }
  }

  private function validate_integer($value,$rule,$required,$scope)
  {
    if(is_string($value)) {
      $value = trim($value);
      if(strlen($value) == 0) {
        if($required) { $this->errors[]   = "$scope is empty"; }
        else {          $this->warnings[] = "$scope is empty"; }
        return;
      }
      if(preg_match('/^-?\d+$/',$value)) {
        $value = intval($value);
      }
    }

    if(!is_integer($value)) {
      $this->error[] = "$scope is not an integer";
      return;
    }

    switch($rule) {
      case "positive":
        if($value < 1) {
          $this->errors[] = "$scope is not a positive value";
        }
        break;

      case "natural":
        if($value < 0) {
          $this->warnings[] = "$scope is a negative value";
        }
        break;
    }
  }

  private function validate_enum($value,$values,$required,$scope)
  {
    if(empty($value)) {
      if($required) { $this->errors[]   = "$scope is empty"; }
      else          { $this->warnings[] = "$scope is empty"; }
      return;
    }

    if(!in_array($value,$values)) {
      $this->errors[] = "$scope has invalid value: $value";
    }
  }

}

