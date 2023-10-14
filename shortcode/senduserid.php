<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('shortcode/_login.elements.php');

start_login_form("Userid/Password Recovery",'senduserid');

add_login_instructions([
  'Please enter the address you provided when you registered to participate in the survey',
  'You will be sent an email with your userid and link to reset your password',
]);

add_login_input("email");

add_login_submit('Send email','senduserid',['cancel'=>True]);

close_login_form();
