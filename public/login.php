<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin login cookie handling
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('user_database.php');
require_once plugin_path('database.php');

const ACTIVE_USER_COOKIE = 'tlc-ttsurvey-active';
const USERIDS_COOKIE = 'tlc-ttsurvey-userids';

function active_userid()
{
  log_dev("active_userid()");
  return $_COOKIE[ACTIVE_USER_COOKIE] ?? null;
}

function cookie_userids()
{
  log_dev("cookie_userids()");
  $userids = $_COOKIE[USERIDS_COOKIE] ?? "{}";
  $userids = json_decode($userids,true);
  return array_filter(
    $userids,
    function ($key) { get_user_post_id($key); }
  );
}

function reset_cookie_timeout() {
  log_dev("reset_cookie_timeout()");
  setcookie(
    USERIDS_COOKIE,
    $_COOKIE[USERIDS_COOKIE] ?? "",
    time() + 86400*365,
  );
}

function add_userid_to_cookie($userid,$active=true)
{
  log_dev("add_userid_to_cookie($userid,$active)");
  $_COOKIE[ACTIVE_USER_COOKIE] = $userid;
  $userids = cookie_userids();
  if(!in_array($userid,$userids)) {
    $userids[] = $userid;
    $_COOKIE[USERIDS_COOKIE] = json_encode($userids);
  }
  save_survey_cookies();
}

function logout_active_user()
{
  log_dev("logout_active_user()");
  unset($_COOKIE[ACTIVE_USER_COOKIE]);
  save_survey_cookies();
}

function resume_survey_as($userid,$case)
{
  log_dev("resume_survey_as($userid,$case)");
  /*
  TODO: Verify userid
  TODO: Verify anonid if specified
  TODO: if anonid was blank, create one now and notify user of new anonid
   */
  add_userid_to_cookie($userid,true);
}

function remove_userid_from_cookie($userid)
{
  log_dev("remove_userid_from_cookie($userid)");
  if( active_userid() == $userid ) {
    unset($_COOKIE[ACTIVE_USER_COOKIE]);
  }
  $userids = cookie_userids();
  unset($userids[$userid]);
  if($userids) {
    $_COOKIE[USERIDS_COOKIE] = json_encode($userids);
  } else {
    unset($_COOKIE[USERIDS_COOKIE]);
  }
  save_survey_cookies();
}

function save_survey_cookies()
{
  log_dev("save_survey_cookies()");
  setcookie( ACTIVE_USER_COOKIE, $_COOKIE[ACTIVE_USER_COOKIE], 0 );
  setcookie( USERIDS_COOKIE, $_COOKIE[USERIDS_COOKIE], time() + 86400*365);
}

reset_cookie_timeout();

function login_init()
{
  log_dev("login_init()");
  $nonce = $_POST['_wpnonce'] ?? '';

  if( wp_verify_nonce($nonce,LOGIN_FORM_NONCE) )
  {
    require_once plugin_path('users.php');

    $action = $_POST['action'] ?? null;
    log_dev("action=$action");
    if( $action == 'resume' ) {
      resume_survey_as($_POST['userid'],$_POST['case']);
    }
    elseif( $action == 'new_user' ) {
      $userid = $_POST['userid'] ?? null;
      $name = $_POST['name'] ?? null;
      $email = $_POST['email'] ?? null;
      $password = $_POST['password'] ?? null;

      if(!is_valid_userid($userid)) {
        // @@@ TODO: Handle bad user userid
        log_warning("Need to add logic for bad new user userid");
      }
      
      if(!is_valid_name($name)) {
        // @@@ TODO: Handle bad user name
        log_warning("Need to add logic for bad new user name");
      }
      
      if(!is_valid_email($email)) {
        // @@@ TODO: Handle bad user email
        log_warning("Need to add logic for bad new user email");
      }
      
      if(!is_valid_password($password)) {
        // @@@ TODO: Handle bad user password
        log_warning("Need to add logic for bad new user password");
      }
      
      log_dev("Registering new user: $name, $email");
      add_new_user($userid,$password,$name,$email);
      add_userid_to_cookie($userid,true);
    }
    elseif( $action == 'resend_userid') {
      require_once plugin_path('include/sendmail.php');
      sendmail_userid_reminder($_POST['email']);
    }
    elseif( $action == 'logout') {
      logout_active_user();
    }
  }
}

add_action('init',ns('login_init'));

//[$userid,$anonid] = create_unique_ids('QQ');
//$login_cookie->add($userid,$anonid,false);
