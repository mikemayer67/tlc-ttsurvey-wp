<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/users.php');

$users_with_email = get_users_by_email($email);

?>

<?=$content?>

<div style='margin-left:1em;'>
<ul>
<li>Your name will appear on survey reports as <b><?=$name?></b>.</li>
<li>The userid you chose for logging into the survey is <b><?=$userid?></b>.</li>
</ul>
</div>

<?php
if(count($users_with_email) > 1) {
  $ids = implode(", ", $users_with_email);
  echo "<div>Other Users with email $email: $ids</div>";
}

require plugin_path("include/sendmail/contacts.php");


