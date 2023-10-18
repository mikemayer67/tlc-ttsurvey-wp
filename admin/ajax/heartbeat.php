<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

function handle_edit_lock_ajax($response,$data)
{
  $key = 'tlc_ttsurvey_lock';
  $query = $data[$key] ?? null;
  if(!$query) { return; }

  $pid = $query['pid'] ?? null;
  if(!$pid) { return; }

  switch( $query['action'] ?? null ) 
  {
  case 'renew':
    wp_set_post_lock($pid);
    break;

  case 'watch':
    // see who has the lock
    $locked_by = wp_check_post_lock($pid);
    if($locked_by) {
      log_info("content form is locked by: $locked_by");
      // it is still locked
      //   return the lock status and name of person who has the lock
      $user = get_userdata($locked_by);
      $username = $user->display_name;
      $response[$key] = array(
        'got_lock'=>false,
        'locked_by'=>$username,
      );
    }
    else
    {
      log_info("content form is no longer locked");
      // no longer locked
      //   acquire the lock and return the new lock status
      wp_set_post_lock($pid);
      $response[$key] = array( 
        'got_lock'=>true,
      );
    }
    break;
  }

  return $response;
}

function handle_heartbeat($response, $data, $screen_id)
{
  $response = handle_edit_lock_ajax($response, $data);

  return $response;
}
