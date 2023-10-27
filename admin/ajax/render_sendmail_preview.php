<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/sendmail.php');

$pid = $_POST['pid'];
$subject = $_POST['subject'];
$content = $_POST['content'] ?? '';

$content = stripslashes($content);
$preview = sendmail_render_preview($pid,$subject,$content);

$response = array( 'ok'=>true, 'preview'=>$preview );
$rval = json_encode($response);
echo $rval;
wp_die();
