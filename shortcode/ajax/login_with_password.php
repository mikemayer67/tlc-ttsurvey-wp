<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');

$userid = adjust_user_input('userid',$_POST['userid']);
$password = adjust_user_input('password',$_POST['password']);
$remember = filter_var($_POST['remember']??false, FILTER_VALIDATE_BOOLEAN);

// Let CookieJar know this is an ajax call.
//   This modifies how login_with_password handles cookies 
$jar = CookieJar::instance(true);

wp_send_json(login_with_password($userid,$password,$remember));
wp_die();
