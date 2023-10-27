`<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('admin/ajax/demo_markdown.php');

$pid = $_POST['pid'] ?? null;
if(!$pid)
{
  log_error("submit_content_form POST is missing pid (post_id)");
  $rval = json_encode(array('ok'=>false, 'error'=>'missing pid (post_id)'));
  echo $rval;
  wp_die();
}

$post = get_post($pid);
$content = json_decode($post->post_content,true);

$sendmail = $content['sendmail'] ?? array();

$rendered = array();
foreach($sendmail as $key=>$content) {
  $rendered[$key] = render_mail_content($key,$content);
}

$response = array(
  'ok'=>true,
  'pid'=>$pid,
  'last_modified'=>get_post_modified_time('U',true,$post),
  'survey'=>$content['survey'],
  'sendmail'=>$sendmail,
  'preview'=>$rendered,
);

$rval = json_encode($response);

echo $rval;
wp_die();


