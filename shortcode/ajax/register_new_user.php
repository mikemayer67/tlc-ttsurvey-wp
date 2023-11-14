<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');

$userid = adjust_login_input('userid',$_POST['userid']);
$password = adjust_login_input('password',$_POST['password']);
$pwconfirm = adjust_login_input('password',$_POST['pwconfirm']);
$username = adjust_login_input('username',$_POST['username']);
$email = adjust_login_input('email',$_POST['email']);
$remember = $_POST['remember'] ?? false;

// Let CookieJar know this is an ajax call.
//   This modifies how register_new_user handles cookies 
$jar = CookieJar::instance(true);

$result = register_new_user(
  $userid,
  $password, $pwconfirm,
  $username,
  $email,
  $remember,
);

echo json_encode($result);
wp_die();
