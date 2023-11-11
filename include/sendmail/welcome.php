<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/users.php');

$email = $message_data['email'];
$userid = $message_data['userid'];
$name = $message_data['username'];

echo $custom_content;
?>

<div style='margin-left:1em;'>
<ul>
<li>Your name will appear on survey reports as <b><?=$name?></b>.</li>
<li>The userid you chose for logging into the survey is <b><?=$userid?></b>.</li>
</ul>
</div>

<?php
$users_with_email = User::from_email($email);
$others = array();
foreach($users_with_email as $user) {
  $id = $user->userid();
  if($id != $userid) { 
    $name = $user->display_name();
    $others[] = "$name ($id)";
  }
}
if($others) {
  echo "<div>The email address $email is also being used by:</div>";
  foreach($others as $other) {
    echo "<div style='margin-left:15px;'>$other</div>";
  }
  echo "<div style='margin-top:4px;'>";
  echo "If this is incorrect and you need help cleaning this up, ";
  echo "please contact one of the admins listed below.</div>";
}

echo "<br>";
require plugin_path("include/sendmail/contacts.php");


