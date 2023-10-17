<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');

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
//   partially escaped.  It escapes quiotes and backslashes, but not 
//   newlines.  Preparing the content for export requires:
//   1) Remove the current escaping from each value in the context array
//   2) Convert th context array to JSON
//   3) Fully escape the JSON for insertion into the wordpress database
//
// 
$content = addslashes(json_encode(array_map('stripslashes',$content)));
$post_data = array( 'ID'=>$pid, 'post_content'=>$content);
$rc = wp_update_post($post_data, true);

if( $rc == $pid )
{
  $response = array('ok'=>true,);
} else {
  $response = array('ok'=>false, $rc->get_error_message(), );
}

$rval = json_encode($response);

echo($rval);
wp_die();


