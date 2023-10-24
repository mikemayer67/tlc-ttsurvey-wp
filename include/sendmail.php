<?php
namespace TLC\TTSurvey;

/**
 * Handle sending of email
 */

if( ! defined('WPINC') ) { die; }

const SENDMAIL_PLACEHOLDERS = array(
  'survey'=>'The name of the survey',
  'name'=>'The participant\'s full name',
  'email'=>'The participant\'s email address',
  'userid'=>'The participant\'s login userid',
  'pwreset'=>'URL for resetting participant\'s password',
);

const SENDMAIL_TEMPLATES = array(
  'welcome' => array (
    'trigger' => 'a new participant registers for the survey',
    'placeholders' => array('survey','name','userid','email'),
  ),
  'recovery' => array(
    'label' => 'Userid/Password Recovery',
    'trigger' => 'a participant requests help with their userid/password',
  ),
);

require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');

function sendmail_pwreset($email)
{
  $survey = current_survey();
  $pid = $survey['post_id'];
  $name = $survey['name'];

  return False;
}

function sendmail_welcome($email, $userid, $firstname, $lastname)
{
  log_dev("Send welcome email to $userid: $email");

  $survey = current_survey();
  $pid = $survey['post_id'];
  $name = $survey['name'];

  $placeholders = array(
    'survey' => $name,
    'email' => $email,
    'userid' => $userid,
    'name' => "$firstname $lastname",
  );

  $post = get_post($pid);
  $content = $post->post_content;
  $content = json_decode($post->post_content,true);
  $md = $content[$key];

  $message = render_markdown($md,$placeholders);
  $headers = array('Content-Type: text/html; charset=UTF-8');

  wp_mail($email,"$name Time & Talent survey",$message,$headers);

  return true;
}
