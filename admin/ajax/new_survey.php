<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/surveys.php');

$name = $_POST['name'] ?? null;
$rc = create_new_survey($name);

echo json_encode(array('ok'=>$rc));
wp_die();


