<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('shortcode/login/_elements.php');

add_status_message();

start_login_form("Userid/Password Recovery",'recovery');

add_login_instructions([
  'Please enter the address you provided when you registered to participate in the survey. ' .
  'You will be sent an email with your userid and link to reset your password.',
]);

add_login_input("email");

add_login_submit('Send email','recovery',true);

close_login_form();

