<?php
namespace TLC\TTSurvey;

/**
 * Handle sending of email
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/markdown.php');

function sendmail_userid($email)
{
  log_dev("Send reminder email to $email");
  return False;
}

function sendmail_welcome($email, $userid, $firstname, $lastname, $token)
{
  log_dev("Send welcome email to $userid: $email");
  _sendmail("welcome",$email,$userid,"$firstname $lastname",$token);
}

function _sendmail($key, $email, $userid, $username, $token)
{
  $survey = current_survey();
  $pid = $survey['post_id'];
  $name = $survey['name'];

  $post = get_post($pid);
  $content = $post->post_content;
  $content = json_decode($post->post_content,true);
  $md = $content[$key];

  $message = render_sendmail_markdown($md,$username,$userid,$email,$token);
  $headers = array('Content-Type: text/html; charset=UTF-8');

  wp_mail($email,"$name Time & Talent survey",$message,$headers);
}
