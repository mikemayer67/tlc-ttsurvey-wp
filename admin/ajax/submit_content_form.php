<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

$pid = $_POST['pid'] ?? null;
if(!$pid)
{
  log_error("submit_content_form POST is missing pid (post_id)");
  wp_send_json_error('missing pid (post_id)');
  wp_die();
}

$content = $_POST['content'] ?? null;
if(!$content)
{
  log_error("submit_content_form POST is missing content");
  wp_send_json_error('missing content');
  wp_die();
}

// Writing the content data to the wordpress database is a bit of a PITA.
//   The content array that comes in the POST from javascript is only
//   partially escaped.  It escapes quotes and backslashes, but not newlines.
//   - Remove the current escaping from each value in the context array
//   - Convert the context array to JSON
//   - Fully escape the JSON for insertion into the wordpress database
$content = stripslashes_deep($content);
$content = json_encode($content);
$content = addslashes($content);

$post_data = array( 'ID'=>$pid, 'post_content'=>$content);
$rc = wp_update_post($post_data, true);

if( $rc == $pid )
{
  $post = get_post($pid);
  $response = array(
    'last_modified'=>get_post_modified_time('U',true,$post),
  );
} else {
  wp_send_json_error($rc->get_error_message());
  wp_die();
}

wp_send_json_success($response);
wp_die();


