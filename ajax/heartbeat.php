<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

function handle_heartbeat($response, $data, $screen_id)
{
  log_dev("handle_heartbeat($response, $data, $screen_id)");
  log_dev("Current user: ".print_r(get_current_user_id(),true));
  log_dev("data: ".print_r($data,true));

  $lock = $data['tlc_ttsurvey_lock'] ?? null;
  if($lock) {
    [$pid,$action] = explode(':',$lock);
    log_dev("pid: $pid");
    log_dev("action: $action");
    if($action == 'hold') {
      wp_set_post_lock($pid);
      $response['tlc_ttsurvey_locked'] = 1;
    } elseif($action == 'watch') {
      $locked = wp_check_post_lock($pid);
      if($locked) {
        $userdata = get_userdata($locked);
        $locked = implode(' ',array($userdata->first_name,$userdata->last_name));
      }
      $response['tlc_ttsurvey_locked'] = $locked;
    }
  }

  log_dev("Response: ".print_r($response,true));
  return $response;
}
