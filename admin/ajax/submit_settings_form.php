<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');

update_options_from_post();
update_survey_status_from_post();

$rval = json_encode(array('ok'=>true));
echo($rval);
wp_die();

