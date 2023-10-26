<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

$pid = $_POST['pid'] ?? null;
$rc = reopen_survey($pid);

echo json_encode(array('ok'=>$rc));
wp_die();

