<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/validation.php');

$response = array();
$token = $_POST['token'];
$userid = adjust_login_input('userid',$_POST['userid']);
$password = adjust_login_input('password',$_POST['password']); 

$user = User::from_userid($userid);
if(!$user) {
  log_dev("reset_password: invalid userid=$userid");
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
  log_dev("reset_password: failed: error=$error");
  echo json_encode(array(
    'ok'=>false,
    'error'=>$error,
  ));
  wp_die();
}

$rval = json_encode(array('ok'=>true));
log_dev("reset_password successful, returning: $rval");

echo $rval;
wp_die();
