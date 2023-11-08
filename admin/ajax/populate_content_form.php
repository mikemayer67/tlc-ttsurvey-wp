<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/sendmail.php');

$pid = $_POST['pid'] ?? null;
if(!$pid)
{
  log_error("submit_content_form POST is missing pid (post_id)");
  $rval = json_encode(array('ok'=>false, 'error'=>'missing pid (post_id)'));
  echo $rval;
  wp_die();
}

$post = get_post($pid);
$content = $post->post_content;
$content = json_decode($content,true);

$survey = $content['survey'] ?? '';

$sendmail = array();
$preview = array();
foreach( array_keys(SENDMAIL_TEMPLATES) as $key ) {
  # note... the field data should be same as that used in
  #   admin/ajax/render_sendmail_preview.php
  $custom_content = $content['sendmail'][$key] ?? '';
  $sendmail[$key] = $custom_content;
  $preview[$key] = sendmail_render_message(
    $key,
    stripslashes($custom_content),
    array(
      'title' => get_post($pid)->post_title,
      'email' => 't.smith@t3mail.net',
      'userid' => 'tsmith13',
      'name' => 'Thomas Smith',
    ),
  );
}

$response = array(
  'ok'=>true,
  'pid'=>$pid,
  'last_modified'=>get_post_modified_time('U',true,$post),
  'survey' => $content['survey'] ?? '',
  'sendmail'=>$sendmail,
  'preview'=>$preview,
);

$rval = json_encode($response);

echo $rval;
wp_die();


