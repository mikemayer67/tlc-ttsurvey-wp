<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$response = array('ok'=>true,);

log_dev("validate_content_form POST: ".print_r($_POST,true));

$error = null;
foreach(['survey','welcome'] as $key) {
  if(!validate_survey_content($key,$_POST[$key],$error))
  {
    $response['ok'] = false;
    $response[$key] = $error;
  }
}
$rval = json_encode($response);
echo($rval);
wp_die();


