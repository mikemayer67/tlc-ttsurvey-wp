<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

function handle_edit_lock_ajax($response,$data)
{
  log_dev("handle_edit_lock_ajax data: ".print_r($data,true));
  $key = 'tlc_ttsurvey_lock';
  $query = $data[$key] ?? null;
  if(!$query) { return; }

  $pid = $query['pid'] ?? null;
  if(!$pid) { return; }

  switch( $query['action'] ?? null ) 
  {
  case 'renew':
    log_dev("renew $pid");
    wp_set_post_lock($pid);
    break;

  case 'watch':
    log_dev("watch $pid");
    // see who has the lock
    $locked_by = wp_check_post_lock($pid);
    if($locked_by) {
      log_dev("locked by: $locked_by");
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
      log_dev("no longer locked");
      // no longer locked
      //   acquire the lock and return the new lock status
      wp_set_post_lock($pid);
      $response[$key] = array( 
        'got_lock'=>true,
      );
    }
    break;
  }

  log_dev("response: ".print_r($response,true));
  return $response;
}

function handle_heartbeat($response, $data, $screen_id)
{
  $response = handle_edit_lock_ajax($response, $data);

  log_dev("heartbeat response: ".print_r($response,true));
  return $response;
}
