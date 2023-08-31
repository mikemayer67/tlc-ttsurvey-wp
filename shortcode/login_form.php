<?php
namespace TLC\TTSurvey;

/**
 * Handle the user login form
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('login.php');
require_once plugin_path('users.php');

$login = LoginCookie::instance();
$userids = $login->all_userids();

$users = Users::instance();

$form_uri=$_SERVER['REQUEST_URI'];
$nonce = wp_nonce_field(LOGIN_FORM_NONCE);

log_dev(print_r($_REQUEST,true));
?>

<div class='form login'>

<?php // Handle any known users in the browser (cookie) history
if($userids) {
  echo "<h2>Welcome Back</h2>";
  foreach ($userids as $userid) {
    $anonid = $login->anonid($userid);
    $submit_value = $userid;

    if( $users->is_valid_userid($userid) ) {
      $name = $users->full_name($userid);
    } else {
      $name = $userid;
    }
?>
    <form class=known-users method='post' action='<?=$form_uri?>'>
      <?=$nonce?>
      <input type=hidden name=action value=resume>
      <input type=hidden name=case value=button>
      <input type=hidden name=userid value='<?=$userid?>'>
<?php if($anonid) { ?>
      <input type=hidden name=anonid value='<?=$anonid?>'>
<?php } ?>
      <input type='submit' value='Resume Survey as <?=$name?>'>
    </form>
<?php
  } // End of loop over known users
} // End of if-block on known users
?>

<h2>I have already registered</h2>
<form class=resume-as method='post' action='<?=$form_uri?>'>
  <p class=info>
    Please enter the user and anonymity codes you were given when you registered.</p>
  <p class=info>
    If you provided an email address, these would have been sent to you by email.</p>
  <?=$nonce?>
  <input type=hidden name=action value=resume>
  <input type=hidden name=case value=entry>
  <table>
    <tr>
      <td class=label>User Code:</td>
      <td class=input>
        <input type=text name=userid placeholder='XX####' maxlength=6></td>
    </tr><tr>
      <td class=label>Anonymity Code:</td>
      <td class=input>
        <input type=text name=anonid placeholder="xx####" maxlength=6></td>
      <td class=info>optional</td>
    </tr>
  </table>
  <div>
    <input type='submit' value="Login to Survey">
  </div>
</form>

<h2>I need to register</h2>
<form class=new-user method='post' action='<?=$form_uri?>'>
  <?=$nonce?>
  <input type=hidden name=action value=new_user>
  <table>
    <tr>
      <td class=label>First Name:</td>
      <td class=input><input class=name type=text name=firstname placeholder=required></td>
    </tr>
    <tr>
      <td class=label>Last Name:</td>
      <td class=input><input class=name type=text name=lastname placeholder=required></td>
    </tr>
    <tr>
      <td class=label>Email:</td>
      <td class=input><input type=email name=email placeholder=optional></td>
    </tr>
  </table>
  <p class=info>
      The email address is optional. It will only be used in conjunction with this survey. If provided, it will be used to send you:
      <ul>
        <li>confirmation of your registration
        <li>comfirmation of your survey submission
        <li>status updates for an incomplete survey
        <li>user code reminders (if requested)
      </ul>
    </p>
  <div>
    <input type='submit' value="Register for Survey">
  </div>
</form>

<h2>I forgot my User Code</h2>
<form class=resend-userid method=post action='<?=$form_uri?>'>
  <?=$nonce?>
  <input type=hidden name=action value='resend_userid'>
  <p class=info>
    If you provided an email address when you registered for the survey, you can
    request to have your user code sent to you by email.</p>
    <table>
      <tr>
        <td class=label>Email:</td>
        <td class=input><input type=email name=email placeholder=required></td>
      </tr>
    </table>
  <div>
    <input type='submit' value="Send my User Code">
  </div>
</form>
  

</div>
