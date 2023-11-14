<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin login cookie handling
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/users.php');
require_once plugin_path('include/validation.php');
require_once plugin_path('shortcode/setup.php');

const ACTIVE_USER_COOKIE = 'tlc-ttsurvey-active';
const ACCESS_TOKEN_COOKIE = 'tlc-ttsurvey-tokens';

/**
 * CookieJar is used to manage the login cookies.
 *
 * CookieJar supports both AJAX and noscript scenarios.
 *   In both cases, it is instantiated with the browser's current cookies
 *   For noscript:
 *   - any changes to the browser cookies are handled immediately
 *   - this must happen before any html is written to standard out
 *   For ajax:
 *   - any changes to the cookies are returned in an array
 *   - these will be passed back in the ajax response
 *   - the javascript that invoked ajax must set the cookies on the browser
 *
 * CookieJar is a singleton class accessed via the instance() method.
 *   To support ajax, instance() should be passed a truthy value.
 **/
class CookieJar
{
  private static $_instance = null;
  private $_ajax = false;
  private $_active_userid = null;
  private $_access_tokens = null;

  public static function instance($ajax=false)
  {
    if(!self::$_instance) { self::$_instance = new CookieJar(); }

    if($ajax) {
      self::$_instance->_ajax = true;
    }

    return self::$_instance;
  }

  private function __construct()
  {
    $this->_active_userid = stripslashes($_COOKIE[ACTIVE_USER_COOKIE]??"");
    $this->_access_tokens = array();
    $tokens = stripslashes($_COOKIE[ACCESS_TOKEN_COOKIE]??"");

    #reset cookie timeout
    $site_path = parse_url(get_site_url(),PHP_URL_PATH);
    setcookie(ACCESS_TOKEN_COOKIE, $tokens, time() + 86400*365, $site_path);

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

  private function _set_cookie($key,$value,$expires)
  {  
    $site_path = parse_url(get_site_url(),PHP_URL_PATH);
    if($this->_ajax) {
      return array($key,$value,$expires,$site_path);
    } else {
      setcookie($key,$value,$expires,$site_path);
    }
    return true;
  }

  public function set_active_userid($userid)
  {
    log_dev("set_active_userid($userid)");
    $this->_active_userid = $userid;
    return $this->_set_cookie(ACTIVE_USER_COOKIE,$userid,0);
  }

  public function clear_active_userid()
  {
    return $this->set_active_userid(null);
  }

  public function get_access_token($userid)
  {
    return $this->_access_tokens[$userid] ?? null;
  }

  public function set_access_token($userid,$token)
  {
    $this->_access_tokens[$userid] = $token;
    return $this->_set_cookie( 
      ACCESS_TOKEN_COOKIE, 
      json_encode($this->_access_tokens), 
      time() + 86400*365,
    );
  }

  public function clear_access_token($userid)
  {
    return $this->set_access_token($userid,null);
  }

  public function access_tokens()
  {
    return $this->_access_tokens;
  }
}


function active_userid()
{
  $rval = CookieJar::instance()->get_active_userid();
  return $rval;
}

function cookie_tokens()
{
  $rval = CookieJar::instance()->access_tokens();
  return $rval;
}


function logout_active_user()
{
  return CookieJar::instance()->clear_active_userid();
}

function start_survey_as($userid)
{
  return CookieJar::instance()->set_active_userid($userid);
}

function resume_survey_as($userid,$token)
{
  log_dev("resume_survey_as($userid,$token)");
  if( validate_user_access_token($userid,$token) ) { 
    return CookieJar::instance()->set_active_userid($userid);
  }
  return false;
}

function remember_user_token($userid,$token)
{
  return CookieJar::instance()->set_access_token($userid,$token);
}

function forget_user_token($userid)
{
  return CookieJar::instance()->clear_access_token($userid);
}


function login_init()
{
  $nonce = $_POST['_wpnonce'] ?? '';

  # need to instantiate the cookie jar during the init phase before
  #   the header has been completed.
  CookieJar::instance();

  $status = $_REQUEST['status'] ?? "";
  if($status) {
    list($level,$msg) = explode("::",$status);
    status_message($msg,$level);
  }

  if( wp_verify_nonce($nonce,LOGIN_FORM_NONCE) )
  {
    require_once plugin_path('include/users.php');

    $action = $_POST['action'] ?? null;
    log_dev("login_init: $action");

    switch($action)
    {
    case 'login':    handle_login();          break;
    case 'resume':   handle_login_resume();   break;
    case 'register': handle_login_register(); break;
    case 'logout':   handle_logout();         break;
    case 'recovery': handle_login_recovery(); break;
    }
  }
}

add_action('init',ns('login_init'));

function handle_login()
{
  $result = login_with_userid(
    adjust_user_input('userid',$_POST['userid']),
    adjust_user_input('password',$_POST['password']),
    filter_var($_POST['remember'] ?? false, FILTER_VALIDATE_BOOLEAN),
  );
  // cookies were handled in login_with_userid, simply need to update status
  if($result['success']) {
    clear_status();
    return true;
  } else {
    set_status_warning($result['error']);
    return false;
  }
}

function login_with_userid($userid,$password,$remember)
{
  $user = User::from_userid($userid);
  if(!$user) {
    return array('success'=>false, 'error'=>'Invalid userid');
  }
  if(!$user->verify_password($password)) {
    return array('success'=>false, 'error'=>'Incorrect password');
  }
  $jar = CookieJar::instance();
  $cookies = array( $jar->set_active_userid($userid) );
  if($remember) {
    $token = $user->access_token();
    $cookies[] = remember_user_token($userid,$token);
  } else {
    $cookies[] = forget_user_token($userid);
  }
  return array('success'=>true, 'cookies'=>$cookies);
}

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
  $result = register_new_user(
    adjust_user_input('userid',$_POST['userid']),
    adjust_user_input('password',$_POST['password']),
    adjust_user_input('password',$_POST['password-confirm']),
    adjust_user_input('username',$_POST['username']),
    adjust_user_input('email',$_POST['email']),
    filter_var($_POST['remember'] ?? false, FILTER_VALIDATE_BOOLEAN),
  );

  // cookies were handled in register_new_user, simply need to update status
  if($result['success']) {
    log_dev("where to go now that I have a new user registered?");
  } else {
    set_status_warning($result['error']);
    set_current_shortcode_page('register');
  }
}

function register_new_user($userid, $password, $pwconfirm, $username, $email, $remember) 
{
  $error='';
  if(!validate_user_input('userid',$userid,$error)) {
    return array(
      'success'=>false, 
      'error'=>"Invalid userid: $error",
    );
  }
  if(!validate_user_input('password',$password,$error)) {
    return array(
      'success'=>false, 
      'error'=>"Invalid password: $error",
    );
  }
  if(!validate_user_input('username',$username,$error)) {
    return array(
      'success'=>false, 
      'error'=>"Invalid name: $error",
    );
  }
  if(!validate_user_input('email',$email,$error)) {
    return array(
      'success'=>false,
      'error'=>"Invalid email: $error",
    );
  }
  if($password != $pwconfirm)
  {
    return array(
      'success'=>false,
      'error'=>'Password did not match its confirmation',
    );
  }

  if(!is_userid_available($userid)) {
    return array(
      'success'=>false,
      'error'=>"Userid '$userid' is already in use",
    );
  }

  $user = User::create($userid,$password,$username,$email);
  $token = $user->access_token();

  log_info("Registered new user $username with userid $userid and token $token");

  $cookies = array( start_survey_as($userid) );
  if($remember) {
    $cookies[] = remember_user_token($userid,$token); 
  }

  if($email) { 
    require_once plugin_path('include/sendmail.php');
    sendmail_welcome($email, $userid, $username, $token); 
  }

  return array('success'=>true, 'cookies'=>$cookies);
}


function handle_logout()
{
  logout_active_user();
}


function handle_login_recovery()
{
  $status = $_POST['status'];

  if($status) { 
    list($level,$msg) = explode('::',$status);
    status_message($msg,$level);
  }
  clear_current_shortcode_page();
}


