<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');

function validate_registration()
{
  log_dev('ajax/validate_registration()');
  log_dev("POST: ".print_r($_POST,true));
  echo("ok");
  wp_die();
}
