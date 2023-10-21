<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

$pid = $_POST['pid'] ?? null;
if(!$pid)
{
  log_error("submit_content_form POST is missing pid (post_id)");
  $rval = json_encode(array('ok'=>false, 'error'=>'missing pid (post_id)'));
  echo $rval;
  wp_die();
}

$content = $_POST['content'] ?? null;
if(!$content)
{
  log_error("submit_content_form POST is missing content");
  $rval = json_encode(array('ok'=>false, 'error'=>'missing content'));
  echo $rval;
  wp_die();
}

// Writing the content data to the wordpress database is a bit of a PITA.
//   The content array that comes in the POST from javascript is only
//   partially escaped.  It escapes quotes and backslashes, but not newlines.
//   - Remove the current escaping from each value in the context array
//   - Convert the context array to JSON
//   - Fully escape the JSON for insertion into the wordpress database
$content = addslashes(json_encode(array_map('stripslashes',$content)));
$post_data = array( 'ID'=>$pid, 'post_content'=>$content);
$rc = wp_update_post($post_data, true);

if( $rc == $pid )
{
  $post = get_post($pid);
  $response = array(
    'ok'=>true,
    'last_modified'=>$post->post_modified_gmt,
  );
} else {
  $response = array('ok'=>false, $rc->get_error_message(), );
}

$rval = json_encode($response);

echo($rval);
wp_die();


