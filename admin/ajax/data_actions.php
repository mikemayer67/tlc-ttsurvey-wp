<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

log_dev("ajax/data_actions POST=".print_r($_POST,true));

$data = array(
  'tlc-ttsurvey-forms'=>"form data",
  'tlc-ttsurvey-ids'=>"ids",
  'form-meta'=>"form meta",
  'user-meta'=>"user meta",
);

echo json_encode(array('ok'=>true, 'data'=>$data));
wp_die();

