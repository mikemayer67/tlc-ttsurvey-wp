<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/sendmail.php');

$pid = $_POST['pid'];
$subject = $_POST['subject'];
$content = $_POST['content'] ?? '';

$template = SENDMAIL_TEMPLATES[$subject];
$message_data = $template['demo_data'];
$message_data['title'] = get_post($pid)->post_title;


$preview = sendmail_render_message(
  $subject,
  stripslashes($content),
  $message_data,
);

$response = array( 'ok'=>true, 'preview'=>$preview );
$rval = json_encode($response);

echo $rval;
wp_die();
