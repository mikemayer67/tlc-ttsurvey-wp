<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('admin/ajax/_validate_survey_data.php');

$data = $_POST['survey_data'];

$data = stripslashes_deep($data);

$findings = validate_survey_data($data);

wp_send_json($findings);
wp_die();
