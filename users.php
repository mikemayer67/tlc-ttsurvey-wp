<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent participant info and login
 */

if( ! defined('WPINC') ) { die; }

require_once 'logger.php';

const USER_TABLE = 'tlc-ttsurvey-users';

/**
 * The user data is storeed in the WP options table as a json encoded array.
 * It's structure looks like the following where:
 *    ? means optional field
 *    + means array
 *    * means optional array
 *
 * userid:
 *   bio:  (null if anonymous or retired userid)
 *     first_name: string
 *     last_name: string
 *     created: timestamp
 *     email?: string
 *   year*:
 *     submitted*:
 *       survey_key: mixed
 *     working*:  
 *       survey_key: mixed
 *   last_reminder: timestamp
 **/

class Users
{
  /**
   * singleton setup
   */
  private static $_instance = null;

  static function instance() {
    if( is_null(self::$_instance) ) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * instance setup
   */
  private $_users = array();

  private function __construct() {
    $users = get_option(USER_TABLE,"{}");
    log_dev("Construct users from: $users");
    $users = json_decode($users,true);
    log_dev("decoded: " . print_r($users,true));

    foreach($users as $userid=>$data) {
      $_users[$userid] = $data;
    }
    log_dev("constructed: " . print_r($_users,true));
  }

  /**
   * public API
   **/

  function all_userids() {
    return array_keys($this->_users);
  }

  function first_name($userid) {
    $bio = $this->_bio();
    if( is_null($bio) ) { return null; }
    return $bio['first_name'] ?? "";
  }

  function last_name($userid) {
    $bio = $this->_bio();
    if( is_null($bio) ) { return null; }
    return $bio['last_name'] ?? "";
  }

  function full_name($userid) {
    $bio = $this->_bio();
    if( is_null($bio) ) { return null; }

    $first_name = $bio['first_name'] ?? "";
    $last_name = $bio['last_name'] ?? "";
    return trim("$first_name $last_name");
  }

  function email($userid) {
    $bio = $this->_bio();
    if( is_null($bio) ) { return null; }
    return $bio['email'] ?? null;
  }

  function created($userid) {
    $bio = $this->_bio();
    if( is_null($bio) ) { return null; }
    return $bio['created'] ?? null;
  }

  function add_user($first_name, $last_name, $email=null)
  {
    $first = trim($first_name);
    $last = trim($last_name);

    // using asserts here as add_user should not be called unless
    //   both first and last name are set
    assert(!empty($first),"Missing first name");
    assert(!empty($last),"Missing last name");

    $bio = array(
      'first_name' => $first,
      'last_name' => $last,
      'created' => time(),
    );

    if(!is_null($email)) {
      $email = trim($email);
      if(!empty($email)) {
        $bio['email'] = $email;
      }
    }

    $prefix = strtoupper($first[0]) . strtoupper($last[0]);
    $userid = $this->_create_userid($prefix);
    $anonid = $this->_create_anonid();

    $this->_users[$userid] = array( 'bio'=>$bio );
    $this->_users[$anonid] = array();

    $this->_save();

    return array($userid,$anonid);
  }


  /**
   * internal functions
   **/

  private function _save()
  {
    log_dev("save users: ".print_r($this->_users,true));
    $new_value = json_encode($this->_users);
    log_dev("encoded: $new_value");
    update_option(USER_TABLE,$new_value);
  }

  private function _bio($userid)
  {
    return $_users[$userid] ?? null;
  }

  private function _create_userid($prefix) {
    do {
      $userid = strtoupper($prefix) . random_int(1000,9999);
    } while( array_key_exists($userid) );
    return $userid;
  }

  private function _create_anonid() {
    do {
      $anonid = (
        chr(97 + random_int(0,25)) .
        chr(97 + random_int(0,25)) .
        random_int(1000,9999)
      );
    } while( array_key_exists($anonid) );
    return $anonid;
  }
}

