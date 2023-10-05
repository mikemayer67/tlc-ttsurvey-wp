<?php
namespace TLC\TTSurvey;

/**
 * Handle sending of email
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');

function sendmail_userid($email)
{
  log_dev("Send reminder email to $email");
  return False;
}

function sendmail_welcome($email, $userid, $firstname, $lastname, $token)
{
  # note token is passed in case we decide to include a password reset link in the email
  
  $year = active_survey_year();
  $name = "$firstname $lastname";

  ob_start();
?>

<html>
  <h1>Welcome to the <?=$year?> Time & Talent survey</h1>
  <p>You have successfully registered to participate in the survey as <?=$name?></p>
  <p>Your userid (<?=$userid?>) will be needed to log back into the survey</p>
</html>

<?php
  $message = ob_get_contents();
  ob_end_clean();

  $headers = array('Content-Type: text/html; charset=UTF-8');

  wp_mail($email,"$year Time & Talent survey",$message,$headers);
}
