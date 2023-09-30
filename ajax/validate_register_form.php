<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$response = array();
$keys = explode(" ","username userid password email");
foreach( $keys as $key )
{
  $value = adjust_login_input($key,$_POST[$key]);
  if($value) {
    $error = '';
    if(!validate_login_input($key,$value,$error)) {
      $response[$key] = $error;
    }
  } elseif($key != "email") {
    $response[$key] = '#empty';
  }
}

$rval = json_encode($response);
echo($rval);
wp_die();
