<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }

$userid = $message_data['userid'];
$key = $message_data['changed'];
$old_value = $message_data['from'];
$new_value = $message_data['to'];

echo $custom_content;
?>

<div style='font-weight:bolder;'>A change has been made to the profile associated with userid: <?=$userid?></div>
<div style='margin-left:1em;'>
<ul>
<li>Old <?=$key?>: <?=$old_value?></li>
<li>New <?=$key?>: <?=$new_value?></li>
</ul>
</div>

<br>
<div>If you did not make this change, please contact one of the following:</div>
<div style='margin-left:1em;'>
<?php
require plugin_path("include/sendmail/contacts.php");
echo "</div>";
