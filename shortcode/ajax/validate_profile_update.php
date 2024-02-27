<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

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

if($key == 'password') {
  $confirm = adjust_user_input('password',$_POST['confirm']);
  if(!$confirm) {
    wp_send_json_error("missing confirmation");
    wp_die();
  }
  if($confirm != $value) {
    wp_send_json_error("confirmation doesn't match");
    wp_die();
  }
}

wp_send_json_success();
wp_die();
