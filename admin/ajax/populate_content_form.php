<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

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

$rval = json_encode(array(
  'ok'=>true,
  'content'=>$content,
));

echo $rval;
wp_die();


