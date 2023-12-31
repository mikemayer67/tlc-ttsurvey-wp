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
    'expires_in' => LOCK_DURATION,
  );
}

function check_content_lock()
{
  $now = current_time('U',true);
  $cur_uid = get_current_user_id();
  $cur_lock = get_option(CONTENT_LOCK_KEY);

  if($cur_lock) {
    [$lock_uid,$lock_expires] = explode(':',$cur_lock);

    if( $now >= $lock_expires ) {
      return array(
        'has_lock' => true,
        'locked_by' => null,
      );
    }
    else if( $lock_uid == $cur_uid ) {
      return array(
        'has_lock' => true,
        'expires_in' => $lock_expires - $now,
      );
    }
    else {
      return array(
        'has_lock' => false, 
        'locked_by' => get_userdata($lock_uid)->display_name,
        'expires_in' => $lock_expires - $now,
      );
    }
  }
  else
  {
    return array(
      'has_lock' => false,
      'locked_by' => null,
    );
  }
}

function release_content_lock()
{
  // verify that we have lock before releasing it...
  $rc = obtain_content_lock();
  if(!$rc['has_lock']) { return false; }

  update_option(CONTENT_LOCK_KEY,'');
  return true;
}


function add_content_lock($lock)
{
  $locked_by = $lock['locked_by'];
  echo "<div class='info lock'>";
  echo "<div>The survey is currently being edited by $locked_by.</div>";
  echo "<div>This tab will automatically refresh when the edit lock is released.</div>";
  echo "</div>";

  enqueue_watch_lock_javascript();
}

function enqueue_watch_lock_javascript()
{
  wp_register_script(
    'tlc_ttsurvey_watch_lock',
    plugin_url('admin/js/watch_content_lock.js'),
    array('jquery'),
    '1.0.3',
    true
  );
  wp_localize_script(
    'tlc_ttsurvey_watch_lock',
    'watch_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('watch_lock',wp_create_nonce('watch_lock')),
    ),
  );
  wp_enqueue_script('tlc_ttsurvey_watch_lock');
}

