<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/users.php');

$data = $_POST['survey_data'];
$data = stripslashes_deep($data);

$findings = validate_survey_data($data);

if(!empty($findings['errors'])) {
  $findings['success'] = false;
  wp_send_json($findings);
  wp_die();
}

$data = json_decode($data,true);
if(is_null($data)) {
  log_error("JSON decoding of data should not have failed.  It already passed validation");
  wp_die();
} 

# do upload stuff here

$old_ids = find_all_ids();

$anonid_map = User::anonid_map($data['userids']);

# @@@ Issue #111: TODO: Map responses to users
#  - Attach responses to $user_data
#  - Attach anonymous responses to $user_data

$error = '';
$survey_id_map = load_all_survey_data($data['surveys'],$error);
if(is_null($survey_id_map)) {
  reset_to_old_data($old_ids);
  send_failure($error);
}

$error = '';
$user_id_map = User::load_all_user_data($data['userids'],$error);
if(is_null($user_id_map)) {
  reset_to_old_data($old_ids);
  send_failure($error);
}

// move old data to trash
log_info("Moving old data to trash");
foreach($old_ids as $post_id) {
  wp_trash_post($post_id);
}

wp_send_json_success();
wp_die();

function find_all_ids()
{
  return array_merge(
    get_posts( array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
      'fields' => 'ids',
    )),
    get_posts( array(
      'post_type' => USERID_POST_TYPE,
      'numberposts' => -1,
      'fields' => 'ids',
    )),
  );
}

function reset_to_old_data($old_ids)
{
  log_warning("Upload failed: reverting to old post content");
  $new_ids = find_all_ids();
  foreach($new_ids as $post_id) {
    if(!in_array($post_id,$old_ids)) {
      wp_delete_post($post_id,true);
    }
  }
}

function send_failure($error)
{
  $findings['success'] = false;
  $findings['error'] = $error;
  $findings['errors'][] = "Upload failed: $error";
  wp_send_json($findings);
  wp_die();
}

