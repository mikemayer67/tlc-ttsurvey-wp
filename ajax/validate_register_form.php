<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/validation.php');

$errors = array();
$keys = explode(" ","username userid password email");
foreach( $keys as $key )
{
  $value = adjust_login_input($key,$_POST[$key]);
  if($value) {
    $error = '';
    if(!validate_login_input($key,$value,$error)) {
      $errors[$key] = $error;
    }
  }
}

$rval = json_encode($errors);
echo($rval);
wp_die();
