<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$data = $_POST['survey_data'];
$data = stripslashes_deep($data);

$findings = validate_survey_data($data);

if(!empty($findings['errors'])) {
  $findings['success'] = false;
  wp_send_json($findings);
  wp_die();
}

# do upload stuff here

wp_send_json_success();
wp_die();
