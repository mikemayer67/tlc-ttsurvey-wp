<?php
namespace TLC\TTSurvey;

/**
 * Setup and querying of plugin database tables
 */

if( ! defined('WPINC') ) { die; }

require_once 'include/logger.php';
require_once 'include/settings.php';

const FORM_TABLE = 'tlc-ttsurvey-forms';

function survey_forms()
{
  $forms = get_option(FORM_TABLE,null);
  if(is_null($forms)) {
    log_info("Adding forms table as ".FORM_TABLE." option");
    $forms = array();
    add_option(FORM_TABLE,$forms);
  }
  return $forms;
}

function survey_years()
{
  $forms = survey_forms();
  $years = array_keys($forms);
  $year = active_survey_year();
  if( ! in_array($year,$years) ) {
    $years[] = $year;
  }
  return $years;
}

function survey_form($year)
{
  $forms = survey_forms();
  if(! array_key_exists($year,$forms) )
  {
    log_info("Adding $year to forms in ".FORM_TABLE." option");
    $forms[$year] = array();
  }
  return $forms[$year];
}


