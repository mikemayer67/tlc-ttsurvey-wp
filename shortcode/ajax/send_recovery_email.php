<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/validation.php');
require_once plugin_path('shortcode/login.php');

$response = array();
$email = adjust_login_input('email',$_POST['email']);
if(!$email) { 
  log_dev("send_recovery_email: empty");
  echo json_encode(array('ok'=>false));
  wp_die();
}

$users = User::from_email($email);
if(!$users) {
  log_dev("send_recovery_email: no matches found for $email");
  echo json_encode(array(
    'ok'=>false,
    'error'=>"unrecognized email $email",
  ));
  wp_die();
}

$reset_keys = array();
foreach($users as $user) {
  $email_token = gen_access_token(10);
  $reset_token = gen_access_token(10);
  $user->set_password_reset_token($reset_token);
  $userid = $user->userid();
  $post_id = $user->post_id();
  $name = $user->display_name();
  $reset_keys[$email_token] = array(
    'reset'=>$reset_token,
    'userid'=>$userid,
    'name'=>$name,
  );
}

if(!sendmail_login_recovery($email,$reset_keys))
{
  log_error("Failed to send login recovery email to $email");
  echo json_encode(array(
    'ok'=>false,
    'error'=>"sendmail failed",
  ));
  wp_die();
}

log_dev("Send_recovery_email reset_keys: ",print_r($reset_keys,true));

echo json_encode(array(
  'ok'=>true,
  'keys'=>$reset_keys,
));

wp_die();



  

