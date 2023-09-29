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
        set_survey_warning($error);
        set_shortcode_page('register');
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
        set_survey_info("Sent userid/password to $email");
      } else {
        set_survey_warning("Unrecognized email address");
        set_shortcode_page("senduserid");
      }
    }
  }
}

function register_new_user(&$error=null)
{
  $name = $_POST['username'];
  $userid = $_POST['userid'];
  $password = $_POST['password'];
  $email = $_POST['email'];

  $validated = (
    validate_and_adjust_username($name,$error) &&
    validate_and_adjust_userid($userid,$error) &&
    validate_and_adjust_password($password,$error) &&
    validate_and_adjust_email($email,$error)
  );
  if(!$validated) {
    $error = "Failed registration attempt:: $error";
    return null;
  }

  if(!is_userid_available($userid)) {
    $error = "Userid '$userid' is already in use";
    return null;
  }

  add_new_user($userid, $password, $name, $email);
  if($email) {
    log_info("Registered new user $name with userid $userid and email='$email'");
  } else {
    log_info("Registered new user $name with userid $userid and no email");
  }

  return true;
}

//[$userid,$anonid] = create_unique_ids('QQ');
//$login_cookie->add($userid,$anonid,false);
