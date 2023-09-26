<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

$form_uri=$_SERVER['REQUEST_URI'];

?>
<form class=tlc-logout method='post' action='<?=$form_uri?>'>
  <?php wp_nonce_field(LOGIN_FORM_NONCE); ?>
  <?=$nonce?>
  <input type=hidden name=action value=logout>
  <input type=submit value="Log Out">
</form>


