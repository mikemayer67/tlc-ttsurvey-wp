<?php
namespace TLC\TTSurvey;

require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

$year = date('Y');
$survey_status = current_survey_status();

if($survey_status==SURVEY_IS_PENDING) { ?>

<div class='w3-container w3-panel w3-card-4 w3-pale-yellow w3-leftbar w3-border-yellow w3-border'>
<p class='w3-margin w3-xlarge'>The <?=$year?> Time and Talent Survey is under construction</p>
</div>

<?php } elseif($survey_status==SURVEY_IS_CLOSED) { ?>

<div class='w3-container w3-panel w3-card-4 w3-pale-red w3-leftbar w3-border-red w3-border'>
<p class='w3-margin w3-xlarge'>The <?=$year?> Time and Talent Survey is now closed</p>
</div>

<?php } else { ?>

<div class='w3-container w3-panel w3-card-4 w3-pale-yellow w3-leftbar w3-border-yellow w3-border'>
<p class='w3-margin w3-xlarge'>The <?=$year?> Time and Talent Survey has not yet been started.</p>
</div>

<?php }


