<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

clear_logger();

echo json_encode(array('ok'=>true));

wp_die();
