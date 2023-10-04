<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin login cookie handling
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/validation.php');

const ACTIVE_USER_COOKIE = 'tlc-ttsurvey-active';
const ACCESS_TOKEN_COOKIE = 'tlc-ttsurvey-tokens';

function active_userid()
{
  $rval = $_COOKIE[ACTIVE_USER_COOKIE] ?? null;
  log_dev("active_userid() => $rval");
  return $rval;
}

function cookie_tokens()
{
  $userids = $_COOKIE[ACCESS_TOKEN_COOKIE] ?? "";
  $userids = json_decode($userids,true) ?? array();
  $rval = array();
  foreach($userids as $userid=>$access_token)
  {
    if(validate_user_access_token($userid,$access_token))
    {
      $rval[$userid] = $access_token;
    }
  }
  log_dev("cookie_tokens() => ".print_r($rval,true));
  return $rval;
}

function reset_cookie_timeout() {
  log_dev("reset_cookie_timeout()");
  setcookie(
    ACCESS_TOKEN_COOKIE,
    $_COOKIE[ACCESS_TOKEN_COOKIE] ?? "",
    time() + 86400*365,
  );
}

function logout_active_user()
{
  log_dev("logout_active_user()");
  unset($_COOKIE[ACTIVE_USER_COOKIE]);
  save_survey_cookies();
}

function resume_survey_as($userid,$token)
{
  log_dev("resume_survey_as($userid,$token)");
  if( !validate_user_access_token($userid,$token) ) { return false; }

  $_COOKIE[ACTIVE_USER_COOKIE] = $userid;

  $tokens = cookie_tokens();
  $tokens[$userid] = $token;
  $_COOKIE[ACCESS_TOKEN_COOKIE] = json_encode($tokens);

  save_survey_cookies();

  return true;
}

function remove_userid_from_cookie($userid)
{
  log_dev("remove_userid_from_cookie($userid)");
  if( active_userid() == $userid ) {
    unset($_COOKIE[ACTIVE_USER_COOKIE]);
  }

  $tokens = cookie_tokens();
  unset($tokens[$userid]);
  $_COOKIE[ACCESS_TOKEN_COOKIE] = json_encode($tokens);

  save_survey_cookies();
}

function save_survey_cookies()
{
  log_dev("save_survey_cookies()");
  setcookie( ACTIVE_USER_COOKIE, $_COOKIE[ACTIVE_USER_COOKIE], 0 );
  setcookie( ACCESS_TOKEN_COOKIE, $_COOKIE[ACCESS_TOKEN_COOKIE], time() + 86400*365);
}

add_action('init',ns('login_init'));

function login_init()
{
  $nonce = $_POST['_wpnonce'] ?? '';

  if( wp_verify_nonce($nonce,LOGIN_FORM_NONCE) )
  {
    require_once plugin_path('include/users.php');

    reset_cookie_timeout();

    $action = $_POST['action'] ?? null;
    log_dev("action=$action");
    if( $action == 'resume' ) {
      $userid = $_POST['userid'];
      $token = $_POST['access_token'];
      if( resume_survey_as($userid,$token) ) {
        log_dev("resuming survey as $userid");
      } else {
        log_dev("invalid access token, removing $userid from cookie");
        remove_userid_from_cookie($userid);
      }
    }
    elseif( $action == 'register')
    {
      $error = '';
      if(register_new_user($error))
      {
        log_dev("where to go now that I have a new user registered?");
      }
      else
      {
        log_dev("need to set status warning: $error");
        set_status_warning($error);
        shortcode_page('register');
      }
    }
    elseif( $action == 'logout') 
    {
      logout_active_user();
    }
    elseif( $action == 'senduserid') 
    {
      require_once plugin_path('include/sendmail.php');
      $email = $_POST['email'];
      if(sendmail_userid($email)) {
        set_status_info("Sent userid/password to $email");
      } else {
        set_status_warning("Unrecognized email address");
        shortcode_page("senduserid");
      }
    }
  }
}

function register_new_user(&$error=null)
{
  $userid = adjust_login_input('userid',$_POST['userid']);
  $password1 = adjust_login_input('password',$_POST['password']);
  $password2 = adjust_login_input('password',$_POST['password-confirm']);
  $firstname = adjust_login_input('name',$_POST['name-first']);
  $lastname = adjust_login_input('name',$_POST['name-last']);
  $email = adjust_login_input('email',$_POST['email']);

  $error='';
  if(!validate_login_input('userid',$userid,$error)) {
    $error = "Failed registration. Invalid userid: $error";
    return false;
  }
  if(!validate_login_input('password',$password1,$error)) {
    $error = "Failed registration. Invalid password: $error";
    return false;
  }
  if(!validate_login_input('name',$firstname,$error)) {
    $error = "Failed registration. Invalid first name";
    return false;
  }
  if(!validate_login_input('name',$lastname,$error)) {
    $error = "Failed registration. Invalid last name: $error";
    return false;
  }
  if(!validate_login_input('email',$email,$error)) {
    $error = "Failed registration. Invalid email: $error";
    return false;
  }
  if($password1 != $password2)
  {
    $error = "Failed registration. Password did not match its confirmation";
    return false;
  }

  if(!is_userid_available($userid)) {
    $error = "Userid '$userid' is already in use";
    return false;
  }

  $token = add_new_user($userid,$password1,$firstname,$lastname,$email);

  log_info("Registered new user $firstname $lastname with userid $userid and token $token");

  $_COOKIE[ACTIVE_USER_COOKIE] = $userid;
  $remember = $_POST['remember'] ?? null;
  if($remember) {
    $tokens = cookie_tokens();
    $tokens[$userid] = $token;
    $_COOKIE[ACCESS_TOKEN_COOKIE] = json_encode($tokens);
  }
  save_survey_cookies();

  $error = '';
  return true;
}

//[$userid,$anonid] = create_unique_ids('QQ');
//$login_cookie->add($userid,$anonid,false);
