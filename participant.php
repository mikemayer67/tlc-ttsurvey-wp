<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent participant info and login
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('logger.php');

/**
 * Attempt to determine current participant from cookie
 */

function get_current_participant()
{
  return $_COOKIE['tlc_ttsurvey_uid'] ?? null;
}

function set_current_participant($userid)
{
  $t = strtotime("+10 months");
  $now = time();
  $delta = $t - $now;
  log_info("Set Current Participant($userid, $t)");
  log_info($_SERVER['SERVER_NAME']);
  $result = setcookie(
    'tlc_ttsurvey_uid',
    $userid,
    $t,
    '/',
    '.'.$_SERVER['SERVER_NAME'],
    false,
    true
  );
  log_info($result);
}
