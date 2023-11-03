<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('shortcode/login/_elements.php');

echo "<noscript>";
echo "<p class='noscript'>Login recovery requires that Javascript be enabled.</p>";
$url = $_SERVER['HTTP_REFERER'];
echo "<a href='$url'>Return to login page</a>";
echo "</noscript>";

start_login_form("Userid/Password Recovery",'sendlogin');

add_login_instructions([
  'Please enter the address you provided when you registered to participate in the survey',
  'You will be sent an email with your userid and link to reset your password',
]);

add_login_input("email");

add_login_submit('Send email','sendlogin',['cancel'=>True]);

close_login_form();

