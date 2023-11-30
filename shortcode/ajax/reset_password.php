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
  wp_send_json_failure("invalid userid $userid");
  wp_die();
}

$error = '';
if($user->update_password($token,$password,$error)) {
  wp_send_json_success();
} else {
  if(!$error) { $error = "Internal error: password not updated"; }
  wp_send_json_failure($error);
}
wp_die();
