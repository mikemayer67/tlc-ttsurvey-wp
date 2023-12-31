<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');

// Let CookieJar know this is an ajax call.
//   This modifies how login_with_password handles cookies 
$jar = CookieJar::instance(true);

wp_send_json(login_with_token($_POST['token']));
wp_die();
