<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$response = array('ok'=true);
$email = adjust_login_input('email',$_POST['email']);
if(!$email) { 
  $response['ok'] = false;
  $response['empty'] = true;
} else {
  $users = User::from_email($email);
  if($users) {
    $response['users'] = $users;
  } else {
    $response['ok'] = false;
    $response['error'] = "Email address $email not recognized";
  }
}

log_dev("Send_recovery_email response: ",print_r($response,true));
$rval = json_encode($response);
echo($rval);
wp_die();



  

