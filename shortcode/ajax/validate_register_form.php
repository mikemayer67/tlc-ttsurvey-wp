<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');
require_once plugin_path('include/users.php');

$response = array();

$keys = array("userid","password","fullname","email");
foreach( $keys as $key )
{
  $value = adjust_user_input($key,$_POST[$key]);
  if(!$value) {
    if($key != "email") {
      $response[$key] = "#empty";
    }
    continue;
  }

  $error = '';
  if(!validate_user_input($key,$value,$error)) {
    $response[$key] = $error;
    continue;
  }

  if($key == "userid" && !is_userid_available($value)) {
    $response[$key] = "already in use";
  }
}

if(!key_exists('password',$response))
{
  $confirm = adjust_user_input('password',$_POST['pwconfirm']);
  if($confirm) {
    $password = adjust_user_input('password',$_POST['password']);
    if($confirm!=$password) {
      $response['password'] = 'does not match confirmation';
    }
  } else {
    $response['password'] = 'missing confirmation';
  }

}

wp_send_json($response);
wp_die();
