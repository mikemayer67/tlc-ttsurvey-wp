<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');

$userid = adjust_login_input('userid',$_POST['userid']);
$password = adjust_login_input('password',$_POST['password']);
$remember = $_POST['remember'] ?? false;

// Let CookieJar know this is an ajax call.
//   This modifies how attempt_login handles cookies 
$jar = CookieJar::instance(true);

$result = attempt_login($userid,$password,$remember);

echo json_encode($result);
wp_die();
