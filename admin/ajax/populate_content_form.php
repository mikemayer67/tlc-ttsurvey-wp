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
  wp_send_json_error('missing pid (post_id)');
  wp_die();
}

$post = get_post($pid);
$content = $post->post_content;
$content = json_decode($content,true);

$survey = $content['survey'] ?? '';

$sendmail = array();
$preview = array();
foreach( SENDMAIL_TEMPLATES as $key=>$template ) {
  $custom_content = $content['sendmail'][$key] ?? '';
  $sendmail[$key] = $custom_content;
  $message_data = $template['demo_data'];
  $message_data['title'] = get_post($pid)->post_title;
  $preview[$key] = sendmail_render_message(
    $key,
    stripslashes($custom_content),
    $message_data,
  );
}

$response = array(
  'pid'=>$pid,
  'last_modified'=>get_post_modified_time('U',true,$post),
  'survey' => $content['survey'] ?? '',
  'sendmail'=>$sendmail,
  'preview'=>$preview,
);

wp_send_json_success($response);
wp_die();


