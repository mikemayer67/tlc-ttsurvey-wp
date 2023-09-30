<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

function validate_registration()
{
  log_dev('ajax/validate_registration()');
  log_dev("POST: ".print_r($_POST,true));
  $response = json_encode(array('hello'=>'world'));
  log_dev("response: $response");
  echo($response);
  wp_die();
}
