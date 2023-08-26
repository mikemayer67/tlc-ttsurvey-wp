<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin login cookie handling
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('logger.php');
require_once plugin_path('database.php');

const LOGIN_COOKIE = 'tlc-ttsurvey-info';

class LoginCookie
{
  /**
   * resets the timeout on the current cookie value to 1 year from now
   **/
  static function reset_timeout() {
    setcookie(
      LOGIN_COOKIE,
      $_COOKIE[LOGIN_COOKIE],
      time() + 86400*365,
    );
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
   * instance setup
   **/
  private $_active_userid = null;
  private $_userid_history = array();

  private function __construct() {
    if(array_key_exists(LOGIN_COOKIE,$_COOKIE)) {
      $userid_cookie = $_COOKIE[LOGIN_COOKIE];
      $ids = json_decode($userid_cookie,true);
      $this->_active_userid = $ids[0];
      $this->_userid_history = $ids[1];
    }
  }

  function active_userid()
  {
    return $this->_active_userid;
  }

  function active_anonid()
  {
    $userid = $this->_active_userid;
    $anonid = $this->_userid_history[$userid] ?? null;
    return $anonid;
  }

  function add($userid,$anonid,$active=true)
  {
    if($active)
    {
      $this->_active_userid = $userid;
    }
    $this->_userid_history[$userid] = $anonid;
    $this->_save();
  }

  function remove($userid)
  {
    if($this->_active_user == $userid) {
      $this->_active_user = null;
    }
    unset($this->_userid_history[$userid]);
    $this->_save();
  }

  private function _save()
  {
    $new_cookie = [
      $this->_active_userid,
      $this->_userid_history,
    ];
    $new_cookie = json_encode($new_cookie);

    setcookie(
      LOGIN_COOKIE,
      $new_cookie,
      time() + 86400*365,
    );
  }
}

$login_cookie = LoginCookie::instance();
$login_cookie->reset_timeout();

// TODO: Parse $_REQUEST to see if we're creating a new userid/anonid entry

//[$userid,$anonid] = $login_cookie->unique_ids('kk');
//$login_cookie->add($userid,$anonid);
