<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('admin/ajax/_validate_survey_data.php');

$data = stripslashes($_POST['survey_data']);
$checksum = $_POST['checksum'];
$expected = hash('crc32b',$data);

log_dev("expected: $expected");

if($checksum !== $expected) {
  return wp_send_json(array("bad_checksum"=>true));
}

$findings = validate_survey_data($data);

wp_send_json($findings);
wp_die();
