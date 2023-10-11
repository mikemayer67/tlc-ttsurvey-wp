<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

function handle_heartbeat($response, $data, $screen_id)
{
  log_dev("handle_heartbeat($response, $data, $screen_id)");
  $n = $data['tlc_ttsurvey_counter'] ?? null;
  log_dev("n: $n");
  if(!is_null($n))
  {
    if($n%2) {
      $n = 3*$n + 1;
    } else {
      $n = $n/2;
    }
    $response['tlc_ttsurvey_new_counter'] = $n;
  }
  log_dev("Response: ".print_r($response,true));
  return $response;
}
