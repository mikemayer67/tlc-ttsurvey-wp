<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

log_dev("handle_ajax  POST: ".print_r($_POST,true));
echo(json_encode(array('status'=>'ok')));
wp_die();
