<?php
namespace TLC\TTSurvey;

/**
 * Handle the user resume form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');
require_once plugin_path('include/users.php');

$tokens = cookie_tokens();

$form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
?>

<div class=resume_form>
  <h3>Please login to begin the survey</h3>
  <form class=resume method=post action='<?=$form_uri?>'>
    <?php wp_nonce_field(LOGIN_FORM_NONCE); ?>
    <input type=hidden name=action value=resume>
    <ul>
    <li><button type=submit name=userid value=userid_1>Continue as User Name</button></li>
    <li><button type=submit name=userid value=userid_2>Continue as No Name</button></li>
    <li><button type=submit name=userid value=userid_3>Continue as My Name</button></li>
    </ul>
  </form>
  <ul>
    <li>Login...</li>
    <li>Register...</li>
  </ul>
</div>
