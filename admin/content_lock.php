<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

const CONTENT_LOCK_KEY = 'tlc_ttsurvey_content_lock';
const LOCK_DURATION = 300;

function obtain_content_lock()
{
  $now = current_time('U',true);
  $cur_uid = get_current_user_id();
  $cur_lock = get_option(CONTENT_LOCK_KEY);

  if($cur_lock) {
    [$lock_uid,$lock_expires] = explode(':',$cur_lock);

    if(($cur_uid != $lock_uid) && ($now<$lock_expires)) {
      // lock is held by someone else
      $locked_by = get_userdata($lock_uid)->display_name;
      return array(
        'has_lock' => false, 
        'locked_by' => get_userdata($lock_uid)->display_name,
        'expires_in' => $lock_expires - $now,
      );
    }
  }

  $lock_expires = $now + LOCK_DURATION;
  $new_lock = implode(':',array($cur_uid,$lock_expires));
  update_option(CONTENT_LOCK_KEY,$new_lock);
  return array(
    'has_lock' => true,
    'until' => $lock_expires,
  );
}

function release_content_lock()
{
  // verify that we have lock before releasing it...
  $rc = obtain_content_lock();
  if(!$rc['has_lock']) { return false; }

  update_option(CONTENT_LOCK_KEY,'');
  return true;
}

