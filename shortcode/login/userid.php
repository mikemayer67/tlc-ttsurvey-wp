<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('shortcode/login/_elements.php');

start_login_form("Survey Login","login");

add_login_input("userid");
add_login_input("password");

add_login_input("remember",array(
  "label" => "Remember Me",
  "value" => True,
  'info' => "<p>Sets a cookie on your browser so that you need not enter your password on fugure logins</p>",
));

add_login_submit("Log in","login");

add_login_links([
  ['forgot login info', 'recovery', 'left'],
  ['register', 'register', 'right'],
]);

close_login_form();
