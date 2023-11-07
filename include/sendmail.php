<?php
namespace TLC\TTSurvey;

/**
 * Handle sending of email
 */

if( ! defined('WPINC') ) { die; }

const SENDMAIL_TEMPLATES = array(
  'welcome' => array(
    'label' => 'Welcome',
    'when' => 'a user registers for the survey',
  ),
  'recovery' => array(
    'label' => 'Login Recovery',
    'when' => 'a user requests help logging in',
  ),
);

require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/markdown.php');


function sendmail_render_message($subject,$content,$data)
{
  $subject_php = plugin_path("include/sendmail/$subject.php");
  if(!file_exists($subject_php)) {
    log_error("Attempt to render invalid sendmail subject ($subject)");
    return null;
  }

  $content = render_markdown($content);
  $email = $data['email'];
  $userid = $data['userid'];
  $name = $data['name'];

  ob_start();
  require $subject_php;
  $message = ob_get_contents();
  ob_end_clean();
  return $message;
}

function sendmail_login_recovery($email)
{
  log_info("Send login recovery email to $email");
  return false;
}

function sendmail_welcome($email, $userid, $firstname, $lastname)
{
  log_info("Send welcome email to $userid: $email");

  $survey = current_survey();
  $post = get_post($survey['post_id']);
  $content = json_decode($post->post_content,true);

  $message = sendmail_render_message(
    'welcome',
    $content['sendmail']['welcome'],
    array(
      'title'=>$survey['name'],
      'email'=>$email,
      'userid'=>$userid,
      'name'=>"$firstname $lastname",
    )
  );

  $headers = array('Content-Type: text/html; charset=UTF-8');

  wp_mail(
    $email,
    $survey['name'] . ' Time & Talent survey',
    $message,
    array(
      'Content-Type: text/html; charset=UTF-8',
    )
  );

  return true;
}
