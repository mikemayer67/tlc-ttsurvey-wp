<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin login cookie handling
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/surveys.php');

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
    if(validate_access_token($userid,$access_token))
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
  if( !validate_access_token($userid,$token) ) { return false; }

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
  log_dev(print_r($_POST,true));
  $nonce = $_POST['_wpnonce'] ?? '';

  if( wp_verify_nonce($nonce,LOGIN_FORM_NONCE) )
  {
    require_once plugin_path('include/users.php');

    reset_cookie_timeout();

    $action = $_POST['action'] ?? null;
    log_dev("action=$action");
    log_dev(print_r($_POST,true));
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
      register_new_user();
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

function register_new_user()
{
  $error = '';
  $name = validate_and_adjust_username($_POST['name'],$error);
  if(!$name)
  {
    set_survey_error($error);
    log_info("Failed registration attempt:: $error (".$_POST['name'].")");
    return null;
  }

  $userid = validate_and_adjust_userid($_POST['userid'],$error);
  if(!$userid)
  {
    set_survey_error($error);
    log_info("Failed registration attempt:: $error (".$_POST['userid'].")");
    return null;
  }

  $password = validate_and_adjust_password($_POST['password'],$error);
  if(!$password)
  {
    set_survey_error($error);
    log_info("Failed registration attempt:: $error");
    return null;
  }

  $email = validate_and_adjust_email($_POST['email'],$error);
  if(!$email)
  {
    set_survey_error($error);
    log_info("Failed registration attempt:: $error (".$_POST['email'].")");
    return null;
  }

  log_info("Registered new user $name with userid $userid and password '$password' and email='$email'");
}

function validate_and_adjust_username($name,&$error=null)
{
  $name = stripslashes($name);              // resolve escaped characters
  $name = trim($name);                      // trim leading/trailing whitespace
  $name = preg_replace('/\s+/',' ',$name);  // condense multiple whitespace
  $name = preg_replace('/\s/',' ',$name);   // only use ' ' for whitespace
  $name = preg_replace('/\'+/',"'",$name);  // condense multiple apostrophes
  $name = preg_replace('/-+/',"-",$name);   // condense multiple hyphens
  $name = preg_replace('/~+/',"~",$name);   // consense multiple tildes

  $names = explode(' ',$name);
  if(count($names)<2) {
    if(!is_null($error)) { $error = "Names must contain both first and last names"; }
    return null;
  }

  $valid_first = "A-Za-z\x{00C0}-\x{00FF}";
  $invalid_first = "'~-";
  $valid = $valid_first . $invalid_first;
  foreach($names as $n)
  {
    $m = array();
    if(preg_match("/([^$valid])/",$n,$m))
    {
      if(!is_null($error)) { $error = "Names cannot contain '$m[1]'"; }
      return null;
    }
    if(preg_match("/^([$invalid_first])/",$n,$m))
    {
      if(!is_null($error)) { $error = "Names cannot start with '$m[1]'"; }
      return null;
    }
  }
  return $name;
}

function validate_and_adjust_userid($userid,&$error=null)
{
  $userid = stripslashes($userid);  // resolve escaped characters
  $userid = trim($userid);          // trim leading/trailing whitespace

  if(strlen($userid)<8 || strlen($userid)>16) 
  {
    if(!is_null($error)) { $error = "Userids must be between 8 and 16 characters"; }
    return null;
  } 
  if(preg_match("/\s/",$userid))
  {
    if(!is_null($error)) { $error = "Userids cannot contain spaces"; }
    return null;
  }
  if(!preg_match("/^[a-zA-Z]/",$userid)) 
  {
    if(!is_null($error)) { $error = "Userids must be begin with a lettter"; }
    return null;
  }
  if(!preg_match("/^[a-zA-Z][a-zA-Z0-9]+$/",$userid)) {
    if(!is_null($error)) { $error = "Userids may only contain letters and numbers"; }
    return null;
  }
  return $userid;
}

function validate_and_adjust_password($password,&$error=null)
{
  $password = stripslashes($password);              // resolve escaped characters
  $password = trim($password);                      // trim leading/trailing whitespace
  $password = preg_replace('/\s+/',' ',$password);  // condense multiple whitespace
  $password = preg_replace('/\s/',' ',$password);   // only use ' ' for whitespace

  if(strlen($password)<8 || strlen($password)>128) 
  {
    if(!is_null($error)) { $error = "Password must be between 8 and 16 characters"; }
    return null;
  } 
  if(!preg_match("/[a-zA-Z]/",$password))
  {
    if(!is_null($error)) { $error = "Passwords must contain at least one letter"; }
    return null;
  }
  if(!preg_match("/^[a-zA-Z0-9 !@%^*_=~,.-]+$/",$password)) 
  {
    if(!is_null($error)) { $error = "Invalid character in password"; }
    return null;
  }
  return $password;
}

function validate_and_adjust_email($email,&$error=null)
{
  $email = stripslashes($email);              // resolve escaped characters
  $email = trim($email);                      // trim leading/trailing whitespace
  $email = filter_var($email,FILTER_VALIDATE_EMAIL);
  if(!$email)
  {
    if(!$is_null) {$error = "Invalid email address";}
    return null;
  }
  return $email;
}

//[$userid,$anonid] = create_unique_ids('QQ');
//$login_cookie->add($userid,$anonid,false);
