<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('shortcode/login/_elements.php');

add_javascript_recommended();
add_status_message();

start_login_form("Register for the Survey","register");

if( $_POST['refresh'] ?? False ) {
  $userid = $_POST['userid'] ?? null;
  $fullname = $_POST['fullname'] ?? null;
  $email = $_POST['email'] ?? null;
  $remember = filter_var($_POST['remember']??false, FILTER_VALIDATE_BOOLEAN);
} else {
  $userid = null;
  $fullname = null;
  $email = null;
  $remember = True;
}

add_login_input("userid",array(
  "label" => "Userid",
  "value" => $userid,
  "info" => <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 16 characters</p>
<p class=info-list><b>must</b> start with a letter</p>
<p class=info-list><b>must</b> contain only letters and numbers</p>
INFO
));

add_login_input("new-password",array(
  "name" => "password",
  "info" => <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 128 characters</p>
<p class=info-list><b>must</b> contain at least one letter</p>
<p class=info-list><b>may</b> contain: !@%^*-_=~,.</p>
<p class=info-list><b>may</b> contain spaces</p>
INFO
));

add_login_input("fullname",array(
  "label" => 'Name',
  "value" => $fullname,
  "info" => <<<INFO
How your name will appear on the survey summary report
<p class=info-list><b>must</b> contain a valid full name</p>
<p class=info-list><b>may</b> contain apostrophes</p>
<p class=info-list><b>may></b> contain hyphens</p>
<p class=info-list>Extra whitespace will be removed</p>
INFO
));

add_login_input("email",array(
  "optional" => True, 
  "value" => $email,
  "info" => <<<INFO
The email address is <b>optional</b>. It will only be used in conjunction with 
this survey. It will be used to send you:
<p class=info-list>confirmation of your registration</p>
<p class=info-list>notifcations on your survey state</p>
<p class=info-list>login help (on request)</p>
INFO
));

# default to true on blank form
# otherwise set to true if currently checked
add_login_checkbox("remember", array(
  "label" => "Remember Me",
  "value" => $remember,
  'info' => "<p>Sets a cookie on your browser so that you need not enter your password on fugure logins</p>",
));

add_login_submit("Register",'register',true);

close_login_form();

