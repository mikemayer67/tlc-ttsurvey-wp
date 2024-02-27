<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/login.php');


$key = $_POST['key'];
$value = adjust_user_input($key,$_POST['value']);

if(!$value) {
  wp_send_json_error('#empty');
  wp_die();
}

$error = '';
if(!validate_user_input($key,$value,$error)) {
  wp_send_json_error($error);
  wp_die();
}

$userid = active_userid();
// @@@ Issue!122 token = active_token();
$user = User::from_userid($userid);

$rval = false;
if($key == 'fullname') {
  $rval = $user->set_fullname($value);
} else if($key == 'email') {
  $rval = $user->set_email($value);
} else if($keey == 'password') {
  $rval = $user->set_password($value);
}

if($rval) {
  wp_send_json_success();
} else {
  wp_send_json_error("Failed to update user profile");
}

wp_die();
