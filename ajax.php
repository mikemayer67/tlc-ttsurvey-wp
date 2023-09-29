<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

/**
 * Create shallow callbacks for ajax calls
 *   Each will use require to populate the ajax response
 *   This will reduce the amount of unnecessary code being loaded
 **/

function ajax_validate_registration()
{
  require plugin_path('ajax/registration.php');
  validate_registration();
}

/**
 * Hook up the callbacks to wp_ajax_nopriv
 **/

add_action(
  'wp_ajax_nopriv_tlc_ttsurvey_validate_registration', 
  'ajax_valiate_registration',
);
