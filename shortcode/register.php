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

$info_icon = plugin_url('img/icons8-info.png');
?>

<div class=login_form>
  <div class='w3-container w3-half w3-margin-top'>
    <header class='w3-container w3-blue-gray'><h3>Need to Register</h3></header>
    <form class='register w3-container w3-card-4' method=post action='<?=$form_uri?>'>
      <?=$nonce?>
      <input type=hidden name=action value=register>
      <div>
        <input class="w3-input" type="text" name=userid required>
        <label>Survey Userid</label>
        <a class="userid info-trigger"><img src='<?=$info_icon?>' width=18 height=18></a>
        <p class='userid info w3-panel w3-pale-yellow w3-border'>
          <i>Remember me</i> will set a cookie on your browswer
          so that you need not enter your password on future logins</p>
        </p>
      </div><p>
        <input class="w3-input" type="password" name=password required>
        <label>Password</label>
        <a class="password info-trigger"><img src='<?=$info_icon?>' width=18 height=18></a>
      </p> 
      </p><p>
        <input class="w3-input" type="email" name=email placeholder='(optional)'>
        <label>Email</label>
        <a class="email info-trigger"><img src='<?=$info_icon?>' width=18 height=18></a>
      <div class='email info w3-panel w3-pale-yellow w3-border'>
        <p>The email address is optional. It will only be used in conjunction with this survey. If provided, it will be used to send you:</p>
        <ul>
          <li>confirmation of your registration
          <li>comfirmation of your survey submission
          <li>status updates for an incomplete survey
          <li>user code reminders (if requested)
        </ul>
      </div>
      <p>
        <input class="w3-check remember-me" type="checkbox" name=remember_me checked="checked">
        <label>Remember me</label>
        <a class="remember-me info-trigger"><img src='<?=$info_icon?>' width=18 height=18></a>
      </p>
      <p class='remember-me info w3-panel w3-pale-yellow w3-border'>
        <i>Remember me</i> will set a cookie on your browswer
        so that you need not enter your password on future logins</p>
      <p>
        <button class='w3-button w3-section w3-blue-gray w3-ripple'>Log in</button>
      </p>
    </form>
  </div>
</div>

