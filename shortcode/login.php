<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');
require_once plugin_path('include/users.php');

$form_uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$nonce = wp_nonce_field(LOGIN_FORM_NONCE);

$info_icon = plugin_url('img/icons8-info.png');
?>

<div class=login_form>
  <div class='w3-container w3-half w3-margin-top'>
    <header class='w3-container w3-blue-gray'><h3>Survey Login</h3></header>
    <form class='login w3-container w3-card-4' method=post action='<?=$form_uri?>'>
      <?=$nonce?>
      <input type=hidden name=action value=login>
      <div class=input>
        <input class="w3-input" type="text" name=userid required>
        <label>Userid</label>
      </div>
      <div class=input>
        <input class="w3-input" type="password" name=password required>
        <label>Password</label>
      </div>
      <div class=input>
        <input class="w3-check remember-me" type="checkbox" name=remember_me checked="checked">
        <label>Remember me</label>
        <a class="remember-me info-trigger"><img src='<?=$info_icon?>' width=18 height=18></a>
        <p class='remember-me info w3-panel w3-pale-yellow w3-border'>
          <i>Remember me</i> will set a cookie on your browswer
          so that you need not enter your password on future logins
        </p>
      </div>
      <div>
        <button class='w3-button w3-section w3-blue-gray w3-ripple w3-block'>Log in</button>
      </div>
      <div class='w3-panel'>
      <div class='w3-left'><a href='<?=$form_uri?>?tlcpage=senduserid'>forgot login info</a></div>
      <div class='w3-right'><a href='<?=$form_uri?>?tlcpage=register'>register</a></div>
      </div>
    </form>
  </div>
</div>

