<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$data = stripslashes($_POST['survey_data']);
$checksum = $_POST['checksum'];
$expected = hash('crc32b',$data);

//if($checksum !== $expected) {
//  log_warning("Invalid checksum for uploading data. Expected $expected. Found $checksum.");
//  return wp_send_json(array("vid"=>$vid, "bad_checksum"=>true));
//}

$findings = validate_survey_data($data);

wp_send_json($findings);
wp_die();
