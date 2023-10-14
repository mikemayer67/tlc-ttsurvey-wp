<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

$form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

?>
<h2>Survey</h2>
<form class=tlc-logout method='post' action='<?=$form_uri?>'>
  <?php wp_nonce_field(LOGIN_FORM_NONCE); ?>
  <?=$nonce?>
  <input type=hidden name=action value=logout>
  <input type=submit value="Log Out">
</form>


