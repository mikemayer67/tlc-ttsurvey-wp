<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/login.php');

$userid = active_userid();
$token = active_token();

if(!validate_user_access_token($userid,$token)) {
  log_warning("Invalid userid/token attempted for drop_user_email ($userid/$token)");
  wp_send_json_error();
  wp_die();
}

$user = User::from_userid($userid);
$user->clear_email();

wp_send_json_success();
wp_die();


