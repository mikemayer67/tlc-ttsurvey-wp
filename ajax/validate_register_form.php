<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

log_dev("ajax_validate_register POST: ".print_r($_POST,true));

$response = array();
$keys = array("userid","password","email");
foreach( $keys as $key )
{
  $value = adjust_login_input($key,$_POST[$key]);
  if($value) {
    $error = '';
    if(!validate_login_input($key,$value,$error)) {
      $response[$key] = $error;
    }
  } elseif($key != "email") {
    $response[$key] = '#empty';
  }
}

$firstname = adjust_login_input('name',$_POST['firstname']);
$lastname = adjust_login_input('name',$_POST['lastname']);
if($firstname) {
  $error = '';
  if(!validate_login_input("name",$firstname,$error)) {
    $response['name'] = $error;
  }
}
if($lastname) {
  $error = '';
  if(!validate_login_input("name",$lastname,$error)) {
    $response['name'] = $error;
  }
}
if(!($firstname && $lastname)) {
  $response['name'] = "#empty";
}

if(!key_exists('password',$response))
{
  $confirm = adjust_login_input('password',$_POST['pw-confirm']);
  if($confirm) {
    $password = adjust_login_input('password',$_POST['password']);
    if($confirm!=$password) {
      $response['password'] = 'does not match confirmation';
    }
  } else {
    $response['password'] = 'missing confirmation';
  }

}

$rval = json_encode($response);
echo($rval);
wp_die();
