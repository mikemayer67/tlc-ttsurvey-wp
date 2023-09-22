<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }


$form_uri=$_SERVER['REQUEST_URI'];
$nonce = wp_nonce_field(LOGIN_FORM_NONCE);

?>
<form class=tlc-logout method='post' action='<?=$form_uri?>'>
  <?=$nonce?>
  <input type=hidden name=action value=logout>
  <input type=submit value="Log Out">
</form>


