<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/validation.php');

$response = array();
$token = $_POST['token'];
$userid = adjust_user_input('userid',$_POST['userid']);
$password = adjust_user_input('password',$_POST['password']); 

$user = User::from_userid($userid);
if(!$user) {
  echo json_encode(array(
    'ok'=>false,
    'error'=>'invalid userid $userid',
  ));
  wp_die();
}

$error = '';
$result = $user->update_password($token,$password,$error);
if(!$result) {
  if(!$error) { $error = "Internal error: password not updated"; }
  echo json_encode(array(
    'ok'=>false,
    'error'=>$error,
  ));
  wp_die();
}

echo json_encode(array('ok'=>true));
wp_die();
