<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login_form_builder.php');

start_login_form("Register for the Survey","register");

$name_info = <<<INFO
How your name will appear on the survey summary report
<p class=info-list><b>must</b> contain a valid name</p>
<p class=info-list><b>may</b> contain apostrophes</p>
<p class=info-list><b>may></b> contain hyphens</p>
<p class=info-list>Extra whitespace will be removed</p>
INFO;

add_login_input("text","username","Name",["info"=>$name_info]);

$userid_info = <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 16 characters</p>
<p class=info-list><b>must</b> start with a letter</p>
<p class=info-list><b>must</b> contain only letters and numbers</p>
INFO;

add_login_input("text","userid","Userid",['info'=>$userid_info]);

$password_info = <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 128 characters</p>
<p class=info-list><b>must</b> contain at least one letter</p>
<p class=info-list><b>may</b> contain: !@%^*-_=~,.</p>
<p class=info-list><b>may</b> contain spaces</p>
INFO;

add_login_input("password","password","Password",['info'=>$password_info]);

$email_info = <<<INFO
The email address is <b>optional</b>. It will only be used in conjunction with 
this survey. It will be used to send you:
<p class=info-list>confirmation of your registration</p>
<p class=info-list>notifcations on your survey state</p>
<p class=info-list>login help (on request)</p>
INFO;

add_login_input("email","email","Email",['optional'=>True,'info'=>$email_info]);

add_login_input("checkbox",'remember-me','Remember me',[
  'checked' => True,
  'info' => "<p>Sets a cookie on your browser so that you need not enter your password on fugure logins</p>",
  ]);

add_login_submit("Register",'register',['cancel'=>True]);

close_login_form();

