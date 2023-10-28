<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/settings.php');

$primary = survey_primary_admin();
$help = array($primary);
foreach(survey_admins('responses') as $uid) {
  if($uid != $primary) { $help[] = $uid; }
}

$content = survey_admins('content');
$tech = survey_admins('tech');

function ids_to_email_list($ids) {
  $rval = array();
  foreach($ids as $id) {
    $user = get_user_by('ID',$id);
    $name = $user->display_name;
    $email = $user->user_email;
    $rval[] = "<a href='mailto:$email'>$name</a>";
  }
  switch(count($rval)) {
  case 1:
    return $rval[0];
    break;
  case 2:
    return "$rval[0] or $rval[1]";
    break;
  default:
    $last = array_pop($rval);
    return implode(', ',$rval) . ", or $last";
  }
}

$help = ids_to_email_list($help);
$content = ids_to_email_list($content);
$tech = ids_to_email_list($tech);
?>

<div style='margin:0;'>
<div><i>For general help with the survey, contact: <?=$help?></i></div>
<div><i>To report an issue with the survey content, contact: <?=$content?></i></div>
<div><i>To report an issue with the survey functionality, contact: <?=$tech?></i></div>
</div>

