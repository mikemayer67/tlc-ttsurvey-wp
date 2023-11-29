<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

$rval = json_encode(array('ok'=>true));
$rval = json_encode(array('ok'=>false, 'warning'=>"That Sucks"));
echo $rval;
wp_die();
