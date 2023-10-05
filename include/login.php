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

class CookieJar
{
  private static $_instance = null;
  private $_active_userid = null;
  private $_access_tokens = null;

  public static function instance()
  {
    if(!self::$_instance) { self::$_instance = new CookieJar(); }
    return self::$_instance;
  }

  private function __construct()
  {
    $this->_active_userid = stripslashes($_COOKIE[ACTIVE_USER_COOKIE]??"");
    log_dev("CookieJar() active_userid: ".$this->_active_userid);
    $this->_access_tokens = array();
    $tokens = stripslashes($_COOKIE[ACCESS_TOKEN_COOKIE]??"");

    #reset cookie timeout
    setcookie(ACCESS_TOKEN_COOKIE, $tokens, time() + 86400*365);

    $tokens = json_decode($tokens,true);
    if($tokens) {
      foreach( $tokens as $userid=>$token ) {
        if(validate_user_access_token($userid,$token))
        {
          $this->_access_tokens[$userid] = $token;
        }
      }
    }
  }

  public function get_active_userid()
  {
    return $this->_active_userid;
  }

  public function set_active_userid($userid)
  {
    $this->_active_userid = $userid;
    setcookie( ACTIVE_USER_COOKIE, $userid, 0 );
  }

  public function clear_active_userid()
  {
    $this->set_active_userid(null);
  }

  public function get_access_token($userid)
  {
    return $this->_access_tokens[$userid] ?? null;
  }

  public function set_access_token($userid,$token)
  {
    $this->_access_tokens[$userid] = $token;
    setcookie( ACCESS_TOKEN_COOKIE, json_encode($this->_access_tokens), time() + 86400*365);
  }

  public function clear_access_token($userid)
  {
    $this->set_access_token($userid,null);
  }

  public function access_tokens()
  {
    return $this->_access_tokens;
  }
}


function active_userid()
{
  $rval = CookieJar::instance()->get_active_userid();
  log_dev("active_userid() => $rval");
  return $rval;
}

function cookie_tokens()
{
  $rval = CookieJar::instance()->access_tokens();
  log_dev("access_tokens() => ".print_r($rval,true));
  return $rval;
}


function logout_active_user()
{
  log_dev("logout_active_user()");
  CookieJar::instance()->clear_active_userid();
}

function start_survey_as($userid)
{
  log_dev("start_survey_as($userid)");
  CookieJar::instance()->set_active_userid($userid);
}

function resume_survey_as($userid,$token)
{
  log_dev("resume_survey_as($userid,$token)");
  if( validate_user_access_token($userid,$token) ) { 
    CookieJar::instance()->set_active_userid($userid);
    return true;
  }
  return false;
}

function remember_user_token($userid,$token)
{
  log_dev("remember_user_token($userid,$token)");
  CookieJar::instance()->set_access_token($userid,$token);
}

function forget_user_token($userid)
{
  log_dev("forget_user_token($userid)");
  CookieJar::instance()->clear_access_token($userid);
}


function login_init()
{
  $nonce = $_POST['_wpnonce'] ?? '';

  # need to instantiate the cookie jar during the init phase before
  #   the header has been completed.
  CookieJar::instance();

  if( wp_verify_nonce($nonce,LOGIN_FORM_NONCE) )
  {
    require_once plugin_path('include/users.php');

    switch($_POST['action'] ?? null)
    {
    case 'resume':
      handle_login_resume();
      break;
    case 'register':
      handle_login_register();
      break;
    case 'logout':
      handle_logout();
      break;
    case 'senduserid':
      handle_send_userid();
      break;
    }
  }
}

add_action('init',ns('login_init'));

function handle_login_resume()
{
  log_dev("handle_login_resume()");
  $userid = $_POST['userid'];
  $token = $_POST['access_token'];
  if( resume_survey_as($userid,$token) ) {
    log_dev("resuming survey as $userid");
  } else {
    log_dev("invalid access token, removing $userid from cookie");
    forget_user_token($userid);
  }
}

function handle_login_register()
{
  log_dev("handle_login_register()");
  $error = '';
  if(register_new_user($error))
  {
    log_dev("where to go now that I have a new user registered?");
  }
  else
  {
    set_status_warning($error);
    shortcode_page('register');
  }
}

function handle_logout()
{
  log_dev("handle_logout()");
  logout_active_user();
}

function handle_send_userid()
{
  log_dev("handle_send_userid");
  require_once plugin_path('include/sendmail.php');
  $email = $_POST['email'];
  if(sendmail_userid($email)) {
    set_status_info("Sent userid/password to $email");
  } else {
    set_status_warning("Unrecognized email address");
    shortcode_page("senduserid");
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

  $remember = $_POST['remember'] ?? False;

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

  start_survey_as($userid);

  if($remember) { remember_user_token($userid,$token); }

  $error = '';
  return true;
}
