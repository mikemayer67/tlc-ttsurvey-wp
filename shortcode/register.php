<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login_form_builder.php');

start_login_form("Register for the Survey","register");

$args = array( "info" => <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 16 characters</p>
<p class=info-list><b>must</b> start with a letter</p>
<p class=info-list><b>must</b> contain only letters and numbers</p>
INFO);
if(key_exists('userid',$_POST)) { $args['value'] = $_POST['userid']; }
add_login_input("text","userid","Userid",$args);

$args = array( "info" => <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 128 characters</p>
<p class=info-list><b>must</b> contain at least one letter</p>
<p class=info-list><b>may</b> contain: !@%^*-_=~,.</p>
<p class=info-list><b>may</b> contain spaces</p>
INFO);
add_login_input("password","password","Password",$args);

$args = array( "info" => <<<INFO
How your name will appear on the survey summary report
<p class=info-list><b>must</b> contain a valid name</p>
<p class=info-list><b>may</b> contain apostrophes</p>
<p class=info-list><b>may></b> contain hyphens</p>
<p class=info-list>Extra whitespace will be removed</p>
INFO);
if(key_exists('username',$_POST)) { $args['value'] = $_POST['username']; }
add_login_input("text","username","Full Name",$args);

$args = array( "optional"=>True, "info" => <<<INFO
The email address is <b>optional</b>. It will only be used in conjunction with 
this survey. It will be used to send you:
<p class=info-list>confirmation of your registration</p>
<p class=info-list>notifcations on your survey state</p>
<p class=info-list>login help (on request)</p>
INFO);
$email = $_POST['email'] ?? null;
if($email) { $args['value'] = $email; }
add_login_input("email","email","Email",$args);

# default to true on blank form
# otherwise set to true if currently checked
add_login_input("checkbox",'remember-me','Remember me',[
  'checked' => key_exists('username',$_POST) ? key_exists('remember-me',$_POST) : True,
  'info' => "<p>Sets a cookie on your browser so that you need not enter your password on fugure logins</p>",
  ]);

add_login_submit("Register",'register',['cancel'=>True]);

close_login_form();

