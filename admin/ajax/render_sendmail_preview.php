<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/sendmail.php');

$pid = $_POST['pid'];
$subject = $_POST['subject'];
$content = $_POST['content'] ?? '';

# note... the field data should be same as that used in
#   admin/ajax/populate_content_form.php
$preview = sendmail_render_message(
  $subject,
  stripslashes($content),
  array(
    'title' => get_post($pid)->post_title,
    'email' => 't.smith@t3mail.net',
    'userid' => 'tsmith13',
    'name' => 'Thomas Smith',
  ),
);

$response = array( 'ok'=>true, 'preview'=>$preview );
$rval = json_encode($response);

echo $rval;
wp_die();
