<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');

update_options_from_post();
update_survey_status_from_post();

wp_send_json_success();
wp_die();

