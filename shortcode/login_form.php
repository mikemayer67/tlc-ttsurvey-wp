<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('login.php');
$login = LoginCookie::instance();
$userids = $login->all_userids();
$nonce = wp_nonce_field(LOGIN_FORM_NONCE);

log_info(print_r($_REQUEST,true));
?>

<div class=tlc-ttsurvey-login-form>

<?php // Handle any known users in the browser (cookie) history
if($userids) {
  $form_action=$_SERVER['REQUEST_URI'];
  echo "<h2>Welcome Back</h2>";
  foreach ($userids as $userid) {
    $anonid = $login->anonid($userid);
    $submit_value = $userid;
?>
    <form class=tlc-known-users method='post' action='<?=$form_uri?>'>
      <?=$nonce?>
      <input type=hidden name=action value=resume>
      <input type=hidden name=case value=button>
      <input type=hidden name=userid value='<?=$userid?>'>
<?php if($anonid) { ?>
      <input type=hidden name=anonid value='<?=$anonid?>'>
<?php } ?>
      <input type='submit' value='Resume survey as <?=$userid?>'>
    </form>
<?php
  } // End of loop over known users
} // End of if-block on known users
?>

<h2>Resume Survey as:</h2>
<form class=tlc-resume-as method='post' action='<?=$form_uri?>'>
  <p class=tlc-info>
    Please enter the user and anonymity codes you were given when you registered.</p>
  <p class=tlc-info>
    If you provided an email address, these would have been sent to you by email.</p>
  <?=$nonce?>
  <input type=hidden name=action value=resume>
  <input type=hidden name=case value=entry>
  <table>
    <tr>
      <td class=tlc-label>User Code:</td>
      <td class=tlc-input>
        <span class=tlc-prefix></span>
        <input type=text name=userid placeholder='XX####' pattern='^[A-Z]{2}\d{4}$' maxlength=6></td>
    </tr><tr>
      <td class=tlc-label>Anonymity Code:</td>
      <td class=tlc-input>
        <span class=tlc-prefix>x_</span>
        <input type=text name=anonid placeholder="####" pattern='^\d{4}$' maxlength=4></td>
      <td class=tlc-info>optional</td>
    </tr>
  </table>
  <div>
    <input type='submit' value="Let's Go">
  </div>
</form>

<h2>Register as New User</h2>
<form class=tlc-new-user method='post' action='<?=$form_uri?>'>
  <?=$nonce?>
  <input type=hidden name=action value=new_user>
  <table>
    <tr>
      <td class=tlc-label>First Name:</td>
      <td class=tlc-input><input type=text name=firstname required></td>
    </tr>
    <tr>
      <td class=tlc-label>Last Name:</td>
      <td class=tlc-input><input type=text name=lastname required></td>
    </tr>
    <tr>
      <td class=tlc-label>email:</td>
      <td class=tlc-input><input type=email name=email></td>
      <td class=tlc-info>optional</td>
    </tr>
  </table>
  <p class=tlc-info>
      The email address is optional. It will only be used in conjunction with this survey. If provided, it will be used to send you:
      <ul>
        <li>confirmation of your registration
        <li>comfirmation of your survey submission
        <li>status updates for an incomplete survey
        <li>user code reminders (if requested)
      </ul>
    </p>
  <div>
    <input type='submit' value="Let's Get Started">
  </div>
</form>

<h2>I forgot my User Code</h2>
<form class=tlc-resend-userid method=post action='<?=$form_uri?>'>
  <?=$nonce?>
  <input type=hidden name=action value='resend_userid'>
  <p class=tlc-info>
    If you provided an email address when you registered for the survey, you can
    request to have your user code sent to you by email.</p>
    <table>
      <tr>
        <td class=tlc-label>email:</td>
        <td class=tlc-input><input type=email name=email required></td>
      </tr>
    </table>
  <div>
    <input type='submit' value="Send my User Code">
  </div>
</form>
  

</div>
