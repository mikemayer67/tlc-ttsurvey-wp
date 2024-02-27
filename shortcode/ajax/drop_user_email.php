<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/login.php');

$userid = active_userid();
// @@@ Issue!122 token = active_token();
$user = User::from_userid($userid);
$user->clear_email();

wp_send_json_success();
wp_die();


