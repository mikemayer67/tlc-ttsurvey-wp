<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

function handle_heartbeat($response, $data, $screen_id)
{
  $lock_query = $data['tlc_ttsurvey_lock'] ?? null;
  if($lock_query) {
    $pid = $lock_query['pid'] ?? 0;
    $is_locked = $lock_query['lock'] ?? null;
    if($is_locked) {
      // survey is currently locked by another user
      //   see if it is still locked
      $locked_by = wp_check_post_lock($pid);
      if($locked_by) {
        // it is still locked
        //   return the lock status and name of person who has the lock
        $user = get_userdata($locked_by);
        $response['tlc_ttsurvey_lock'] = array(
          'has_lock'=>0, 
          'locked_by'=>$user->display_name,
        );
      }
      else
      {
        // no longer locked
        //   acquire the lock and return the new lock status
        wp_set_post_lock($pid);
        $response['tlc_ttsurvey_lock'] = array('has_lock'=>1);
      }
    }
    else 
    {
      // survey is not currently locked by anyone else
      //   establish/refresh our lock on it
      wp_set_post_lock($pid);
      $response['tlc_ttsurvey_lock'] = array('has_lock'=>1);
    }
  }

  return $response;
}
