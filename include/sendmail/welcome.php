<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }
?>

<?=$content?>

<div style='margin-left:1em;'>
<ul>
<li>Your name will appear on survey reports as <b><?=$name?></b>.</li>
<li>The userid you chose for logging into the survey is <b><?=$userid?></b>.</li>
</ul>
</div>

<?php
require plugin_path("include/sendmail/contacts.php");


