<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/login.php');
$form_uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$btn_classes = 'w3-button w3-section w3-ripple w3-right w3-margin-left';

$nonce = wp_nonce_field(LOGIN_FORM_NONCE);
?>

<div class=login_form>
  <div class='w3-container w3-half w3-margin-top'>
    <header class='w3-container w3-blue-gray'><h3>Userid/Password Rcovery</h3></header>
    <form class='senduserid w3-container w3-card-4' method=post action='<?$form_uri?>'>
      <?=$nonce?>
      <div 'w3-container'>
      <div class=instruction>
        Please enter the address you provided when you registered to 
        participate in the survey.
      </div>
      <div class=instruction>
        You will be sent an email with your userid and link to reset your password.
      </div>
      </div>
      <div class=input>
        <input class="w3-input" type="email" name=email required>
        <label>Email</label>
      </div>
      <div class='w3-bar'>
        <button class='<?=$btn_classes?> w3-blue-gray' name=action value=senduserid>
          Send email
        </button>
        <button class='<?=$btn_classes?> w3-light-gray' name=action value='' formnovalidate>
          Never mind
        </button>
      </div>
    </form>
  </div>
</div>
