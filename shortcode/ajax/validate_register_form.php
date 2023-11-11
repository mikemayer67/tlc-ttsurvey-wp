<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$response = array();
$keys = array("userid","password","username","email");
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

if(!key_exists('password',$response))
{
  $confirm = adjust_login_input('password',$_POST['pwconfirm']);
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
