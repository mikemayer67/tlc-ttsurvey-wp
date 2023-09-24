<?php
namespace TLC\TTSurvey;

/**
 * Handle sending of email
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

function sendmail_userid($email)
{
  log_dev("Send reminder email to $email");
  return False;
}
