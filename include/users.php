<?php
namespace TLC\TTSurvey;


/**
 * TLC Time and Talent participant info and login
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

/**
 * The survey participant information is stored as wordpress posts 
 * using the custom post type of tlc-ttsurvey-id.  Each post corresponds
 * to a single participant userid or anonymous id (anonid)
 *
 * The post title contains the userid or anonid
 * The post content contains validation information
 *   For user posts, contains a hash of the password
 *   For anonymous posts, contains a hash of the user's post id with the anonid
 *
 * Post metadata is used to provide additional user information
 *   - name: actual name, not userid
 *   - email: optional
 *   - access token: set in cookie
 * There is no biographical metadata associated with anonymous posts
 *
 * Survey responses from each participant is attached to either a
 *   user post or an anonymous post as meta data. The metadata key 
 *   indicates the survey status.
 *   - Submitted responses use the survey year as the metadata key
 *   - Working drafts use 'working' as the metadata key
 *
 * The following conventions are used in this module:
 *   userid:
 *     - unique ID associated with each survey participant
 *     - selected by the participant when they registered for the survey
 *     - stored in the title attribute of wp_post table
 *   user post id:
 *     - the index of the the user's entry in the wp_post table
 *     - used internal to wordpress and the plugin
 *   user name:
 *     - the participant's IRL name as it will appear on the survey
 *       summary report.
 *     - provided by the participant when they register for the survey
 *     - may be modified by the participant once they have logged in
 *     - stored as wordpress post metadata
 *   user email:
 *     - the participant's email address (optional)
 *     - provided by the participant when they register for the survey
 *     - may be added/modified by the participant once they have logged in
 *     - may be removed by the participant once they have logged in
 *     - stored as wordpress post metadata
 *   user password:
 *     - the participant's password for logging into the survey
 *     - provided by the participant when they register for the survey
 *     - may be modified by the participant once they have logged in
 *     - stored internally as a one-way hash of the password
 *   access token:
 *     - used to enable use of cookies to log the user in without a password
 *     - generated by the plugin when the participant registers for the survey
 *     - stored in a cookie along with the userid if cookies are enabled
 *     - is not provided to participant (unless they know how to view 
 *       cookie content in the browser)
 *     - may be regenerated by the participant once they have logged in
 *   anonid:
 *     - similar to the userid, but is associated with anonymous responses
 *     - assigned when the user is added upon registration
 *     - used internal to the plugin for associating the participant
 *       with their anonymous responses
 *     - association between userid/anonid is never included in any logs
 *       or wordpress internal tables other than as described below:
 *     - linkage between userid and anonid is obfuscated through a 
 *       password hash of the user's post id and the anonid.  While this
 *       is not perfect security, it will require a degree of effort to
 *       map the relationship.  It will not be available to a casual
 *       viewer of the wordpress database.
 **/

/**
 * Register the custom post type
 **/

const USERID_POST_TYPE = 'tlc-ttsurvey-id';

function register_userid_post_type()
{
  register_post_type( USERID_POST_TYPE,
    array(
      'labels' => array(
        'name' => 'TLC TTSurvey Participants',
        'singular_name' => 'Participant',
        'add_new' => 'New Participant',
        'add_new_item' => 'Add New Participant',
        'edit_item' => 'Edit Participant',
        'new_item' => 'New Participant',
        'view_item' => 'View Participants',
        'search_items' => 'Search Participants',
        'not_found' =>  'No Participants Found',
        'not_found_in_trash' => 'No Participants found in Trash',
      ),
      'has_archive' => false,
      'public' => false,
      'show_ui' => true,
      'show_in_rest' => false,
      'show_in_menu' => false,
    ),
  );
}

function users_init()
{
  register_userid_post_type();
}

function users_activate()
{
  log_info("Users Activate");
  register_userid_post_type();
  flush_rewrite_rules();
}

function users_deactivate()
{
  log_info("Users Deactivate");
  unregister_post_type(USERID_POST_TYPE);
}

add_action('init',ns('users_init'));


/**
 * Input validation
 **/

class UserName
{
  private $value = null;
  private $error = null;

  public function __construct($name) {
    $name = stripslashes($name);
    $name = trim($name);                      // trim leading/trailing whitespace
    $name = preg_replace('/\s+/',' ',$name);  // condense multiple whitespace
    $name = preg_replace('/\s/',' ',$name);   // only use ' ' for whitespace
    $name = preg_replace('/\'+/',"'",$name);  // condense multiple apostrophes
    $name = preg_replace('/-+/',"-",$name);   // condense multiple hyphens
    $name = preg_replace('/~+/',"~",$name);   // consense multiple tildes

    $names = explode(' ',$name);
    if(count($names)<2) {
      $this->error = "Names must contain both first and last names";
      return;
    }

    $valid = "A-Za-z\x{00C0}-\x{00FF}'~-";
    $invalid_first = "'~-";
    foreach($names as $n)
    {
      $m = array();
      if(preg_match("/([^$valid])/",$n,$m))
      {
        $this->error = "Names cannot contain '$m[1]'";
        return;
      }
      if(preg_match("/^([$invalid_first])/",$n,$m))
      {
        $this->error = "Namse cannot start with '$m[1]'";
        return;
      }
    }

    $this->value = $name;
  }

  public function is_valid() { return is_null($this->error); }
  public function error()    { return $this->error; }
  public function value()    { return $this->value; }
}

class UserID
{
  private $value = null;
  private $error = null;

  public function __construct($userid)
  {
    $userid = trim($userid);
    $userid = stripslashes($userid);

    if(strlen($userid)<8 || strlen($userid)>16) 
    {
      $this->error = "Userids must be between 8 and 16 characters";
    } 
    elseif(preg_match("/\s/",$userid))
    {
      $this->error = "Userids cannot contain spaces";
    }
    elseif(!preg_match("/^[a-zA-Z]/",$userid)) 
    {
      $this->error = "Userids must be begin with a lettter";
    }
    elseif(!preg_match("/^[a-zA-Z][a-zA-Z0-9]+$/",$userid)) {
      $this->error = "Userids may only contain letters and numbers";
    }
    else
    {
      $this->value = $userid;
    }
  }

  public function is_valid() { return is_null($this->error); }
  public function error()    { return $this->error; }
  public function value()    { return $this->value; }
}

function is_valid_userid($userid)
{
  $userid = trim($userid);
  log_dev("is_valid_userid($userid)");
  return true;
}

function is_valid_password($password)
{
  $password = trim($password);
  log_dev("is_valid_password($password)");
  return true;
}

function is_valid_email($email)
{
  $email = trim($email);
  log_dev("is_valid_email($email)");
  if(empty($email)) { return true; }

  return true;
}

function validate_password($userid,$password)
{
  log_dev("validate_password($userid,*****);");
  $post = get_user_post($userid);
  if(!$post) { 
    log_info("Failed to validate password:: Invalid userid $userid");
    return false;
  }
  $pw_hash = $post->content;
  if(!password_verify($userid,$pw_hash)) {
    log_info("Failed to validate password:: Incorrect password for $userid");
    return false;
  }
  log_dev(" => password validated for $userid");
  return true;
}

function validate_access_token($userid,$token)
{
  log_dev("valiate_access_token($userid,$token)");
  $post_id = get_user_post_id($userid);
  log_dev("  post_id: $post_id");
  if(!$post_id) { return false; }

  $expected_token = get_post_meta($post_id,'access_token');
  log_dev("  expected token: $expected_token");
  return $token == $expected_token;
}

/**
 * User info lookup functions
 **/

function get_user_post($userid)
{
  log_dev("get_user_post($userid");
  $posts = get_posts(
    array(
      'post_type' => USERID_POST_TYPE,
      'numberposts' => -1,
      'title' => $userid,
    )
  );
  if(count($posts) > 1) {
    # log error both to the plugin log and to the php error log
    log_error("Multiple posts associated with userid $userid");
    error_log("Multiple posts associated with userid $userid");
    die;
  }
  if(!$posts) { 
    log_info("No post found for userid $userid");
    return null;
  }
  return $posts[0];
}

function get_user_post_id($userid)
{
  log_dev("get_user_post_id($userid)");
  $post = get_user_post($userid);
  if(!$post) { return null; }

  $post_id = $post->ID;
  log_dev(" => $post_id");
  return $post_id;
}

function get_user_name($userid)
{
  log_dev("get_user_name($userid)");
  $post_id = get_user_post_id($userid);
  if(!$post_id) { return null; }

  $name = get_post_meta($post_id,'name');
  log_dev(" => $name");
  return $name;
}

function get_user_email($userid)
{
  log_dev("get_user_email($userid)");
  $post_id = get_user_post_id($userid);
  if(!$post_id) { return null; }

  $email = get_post_meta($post_id,'email');
  log_dev(" => $email");
  return $email;
}

function get_user_anonid($userid)
{
  log_dev("get_user_anonid($userid)");
  $user_post_id = get_user_post_id($userid);
  if(!$user_post_id) { return null; }

  $posts = get_posts(
    array(
      'post_type' => USERID_POST_TYPE,
      'numberposts' => -1,
    )
  );

  foreach($posts as $post) {
    $anonid = $post->title;
    $hash = $post->content;
    if(password_verify("$anonid/$user_post_id",$hash)) {
      log_dev(" => anonid found for $userid");
      return $anonid;
    }
  }
  log_error("No anonid found for $userid");
  return null;
}

/**
 * Functions to add new users
 **/

function is_userid_available($userid)
{
  log_dev("is_userid_available($userid)");
  assert(is_valid_userid($userid),"Invalid userid");
  $post_id = get_user_post_id($userid);
  if($post_id) {
    log_info("  Userid $userid is currently in use");
    return false;
  }
  return true;
}

function gen_access_token()
{
  $access_token = '';
  $token_pool = '123456789123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  for($i=0; $i<25; $i++) {
    $index = rand(0,strlen($token_pool)-1);
    $access_token .= $token_pool[$index];
  }
  return $access_token;
}

function add_new_user($userid, $password, $name, $email=null)
{
  log_dev("add_user($userid, $password, $name, $email)");
  $userid = trim($userid);
  $password = trim($password);
  $name = trim($name);
  $email = trim($email);

  // using asserts here as all of this info should have been checked
  // prior ta calling this function... but to protect the database
  // we're checking here before adding a new wp_post entry
  assert(is_valid_userid($userid), "Invalid userid: '$userid'");
  assert(is_valid_name($name),"Invalid name: '$name'");
  assert(is_valid_password($password), "Invalid password: '$pasword'");
  assert(is_valid_email($email), "Invalid email address: '$email'");
  assert(is_userid_available($userid), "Userid $userid is already in use");

  $user_hash = password_hash($password,PASSWORD_DEFAULT);

  $post_args = array(
    'post_type' => USERID_POST_TYPE,
    'post_title' => $userid,
    'post_content' => $user_hash,
    'post_status' => 'publish',
  );
  $user_post_id = wp_insert_post($post_args,true);
  log_dev("User added ($user_post_id): ".print_r($post_args,true));

  $name_id = update_post_meta($user_post_id,'name',$name);
  log_dev("Added name ($name_id): '$name'");

  if($email) {
    $email_id = update_post_meta($user_post_id,'email',$email);
    log_dev("Added email ($email_id): '$email'");
  } else {
    log_dev("No email added");
  }
  
  $access_token = gen_access_token();

  $access_token_id = update_post_meta($user_post_id,'access_token',$access_token);
  log_dev("Access token added ($access_token_id): '$access_token'");

  do {
    $anonid = 'anon_'.random_int(100000,999999);
  } while( get_user_post_id($anonid) );

  $anon_hash = password_hash("$anonid/$user_post_id",PASSWORd_DEFAULT);

  $anon_args = array(
    'post_type' => USERID_POST_TYPE,
    'post_title' => $anonid,
    'post_content' => $anon_hash,
    'post_status' => 'publish',
  );

  $anon_post_id = wp_insert_post($anonid_args,true);
  log_dev("Anonymous id added for $userid");
}

/**
 * Functions to update user attributes
 **/

function update_user_name($userid,$name)
{
  log_dev("update_user_name($userid)");
  $name = trim($name);
  $user_post_id = get_user_post_id($userid);
  if($user_post_id) { 
    assert(is_valid_name($name),"Invalid name: '$name'");
    update_post_meta($user_post_id,'name',$name);
  } else {
    log_warning("Attempt to update name for invalid userid $userid");
  }
}

function update_user_email($userid,$email)
{
  log_dev("update_user_email($userid)");
  $email = trim($email);
  $user_post_id = get_user_post_id($userid);
  if($user_post_id) { 
    assert(is_valid_email($email),"Invalid email: '$email'");
    if($email) {
      update_post_meta($user_post_id,'email',$email);
    } else {
      delete_post_meta($user_post_id,'email');
    }
  } else {
    log_warning("Attempt to update email for invalid userid $userid");
  }
}

function update_user_password($userid, $password)
{
  log_dev("update_user_password($userid)");
  $password = trim($password);
  $user_post_id = get_user_post_id($userid);
  if($user_post_id) {
    assert(is_valid_password($password), "Invalid password");
    $update_args = array(
      'ID' => $post->ID,
      'post_content' => password_hash($password),
    );
    log_dev("  update_args: ".print_r($update_args.true));
    $updated_post_id = wp_update_post($update_args);
    if($updated_post_id == $user_post_id) {
      log_dev("updated password for $userid");
    } else {
      log_warning("Failed to update password for $userid");
    }
  } else {
    log_warning("Attempt to update password for invalid userid $userid");
  }
}

function regenerate_user_access_token($userid)
{
  log_dev("regenerate_user_access_token($userid)");
  $user_post_id = get_user_post_id($userid);
  if($user_post_id) {
    $access_token = gen_access_token();
    update_post_meta($user_post_id,'access_token',$access_token);
    log_dev("Updated access token for $userid to $access_token");
  } else {
    log_wrning("Attempt to regenerate access token for invalid userid $userid");
  }
}
