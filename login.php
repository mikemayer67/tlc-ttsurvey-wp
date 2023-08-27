<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin login cookie handling
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('logger.php');
require_once plugin_path('database.php');

const ACTIVE_USER_COOKIE = 'tlc-ttsurvey-active';
const USERIDS_COOKIE = 'tlc-ttsurvey-userids';

class LoginCookie
{
  /**
   * instance setup
   **/
  private $_active_userid = null;
  private $_userids = array();

  private function __construct() {
    $this->_active_userid = $_COOKIE[ACTIVE_USER_COOKIE] ?? null;

    $userids = $_COOKIE[USERIDS_COOKIE] ?? "{}";
    $userids = json_decode($userids,true);
    $this->_userids = $userids;
  }

  /**
   * singleton setup
   **/
  private static $_instance = null;

  static function instance() {
    if( self::$_instance == null ) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * resets the timeout on the current cookie value to 1 year from now
   **/
  static function reset_timeout() {
    setcookie(
      USERIDS_COOKIE,
      $_COOKIE[USERIDS_COOKIE],
      time() + 86400*365,
    );
  }

  function active_userid()
  {
    return $this->_active_userid;
  }

  function all_userids()
  {
    return array_keys($this->_userids);
  }

  function active_anonid()
  {
    return $this->anonid($this->_active_userid);
  }

  function anonid($userid)
  {
    return $this->_userids[$userid] ?? null;
  }

  function add($userid,$anonid,$active=true)
  {
    if($active)
    {
      $this->_active_userid = $userid;
    }
    $this->_userids[$userid] = $anonid;
    $this->_save();
  }

  function remove($userid)
  {
    if($this->_active_user == $userid) {
      $this->_active_user = null;
    }
    unset($this->_userids[$userid]);
    $this->_save();
  }

  private function _save()
  {
    $userids = json_encode($this->_userids);
    log_info("save cookie for userids: $userids");
    setcookie( USERIDS_COOKIE, $userids, time() + 86400*365 );
    setcookie( ACTIVE_USER_COOKIE, $this->_active_userid, 0 );
  }

  function unique_ids($prefix) {
    $userid = strtoupper($prefix) . random_int(1000,9999);
    $anonid = 'xx' . random_int(1000,9999);
    $existing = all_userids();
    if( in_array($userid,$existing) || in_array($anonid,$existing) ) {
      return $this->unique_ids($prefix);
    }
    return [$userid,$anonid];
  }

}

$login_cookie = LoginCookie::instance();
$login_cookie->reset_timeout();

// TODO: Parse $_REQUEST to see if we're creating a new userid/anonid entry

//[$userid,$anonid] = $login_cookie->unique_ids('kk');
//$login_cookie->add($userid,$anonid);
