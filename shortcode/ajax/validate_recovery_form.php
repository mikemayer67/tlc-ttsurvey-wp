<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$response = array('ok'=>true);
$email = adjust_login_input('email',$_POST['email']);
if(!$email) { 
  $response['ok'] = false;
  $resposse['empty'] = true;
}
else
{
  $error = '';
  if(!validate_login_input('email',$email,$error)) {
    $response['ok'] = false;
    $response['error'] = $error;
  }
}

$rval = json_encode($response);
echo($rval);
wp_die();
