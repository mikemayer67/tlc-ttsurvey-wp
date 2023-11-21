<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/sendmail.php');
require_once plugin_path('include/validation.php');
require_once plugin_path('shortcode/login.php');

$email = adjust_user_input('email',$_POST['email']);
if(!$email) { 
  echo json_encode(array('ok'=>false));
  wp_die();
}

$users = User::from_email($email);
if(!$users) {
  echo json_encode(array(
    'ok'=>false,
    'error'=>"warning::unrecognized email $email",
  ));
  wp_die();
}

$expires = current_time('U',true) + LOGIN_RECOVERY_TIMEOUT;
$tokens = array();
foreach($users as $user) {
  $email_token = gen_access_token(10);
  $reset_token = gen_access_token(10);
  $user->set_password_reset_token($reset_token,$expires);
  $userid = $user->userid();
  $post_id = $user->post_id();
  $fullname = $user->fullname();
  $tokens[$email_token] = array(
    'reset'=>$reset_token,
    'userid'=>$userid,
    'fullname'=>$fullname,
  );
}

if(!sendmail_login_recovery($email,$tokens))
{
  log_error("Failed to send login recovery email to $email");
  echo json_encode(array(
    'ok'=>false,
    'error'=>"error::internal error: failed to send email",
  ));
  wp_die();
}

echo json_encode(array(
  'ok'=>true,
  'tokens'=>$tokens,
  'expires'=>$expires,
));

wp_die();



  

