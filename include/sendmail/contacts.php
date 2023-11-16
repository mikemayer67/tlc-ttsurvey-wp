<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/settings.php');

$help = survey_admin_contacts('general');
$content = survey_admin_contacts('content');
$tech = survey_admin_contacts('tech');
?>

<div style='margin:0;'>
<div><i>For general help with the survey, contact: <?=$help?></i></div>
<div><i>To report an issue with the survey content, contact: <?=$content?></i></div>
<div><i>To report an issue with the survey functionality, contact: <?=$tech?></i></div>
</div>

