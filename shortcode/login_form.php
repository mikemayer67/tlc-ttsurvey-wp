<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');
require_once plugin_path('include/users.php');

$form_uri=$_SERVER['REQUEST_URI'];
$nonce = wp_nonce_field(LOGIN_FORM_NONCE);
?>

<div class=login_form>
  <h3>Please login to begin the survey</h3>
  <form class=login method=post action='<?=$form_uri?>'>
    <?=$nonce?>
    <input type=hidden name=action value=login>
    <ul>
    </ul>
    <input type=submit value="Login"/>
  </form>
</div>

