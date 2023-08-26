<?php
namespace TLC\TTSurvey;

/**
 * Setup and querying of plugin database tables
 */

if( ! defined('WPINC') ) { die; }

require_once 'logger.php';
require_once 'settings.php';

const USER_TABLE = 'tlc-ttsurvey-users';
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

function survey_users()
{
  $forms = get_option(USER_TABLE,null);
  if(is_null($forms)) {
    log_info("Adding userss table as ".USER_TABLE." option");
    $forms = array();
    add_option(USER_TABLE,$forms);
  }
  return $forms;
}

function survey_years()
{
  $forms = survey_forms();
  $years = array_keys($forms);
  $year = Settings::active_year();
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

/*
 * returns all user ids and anonymous ids ever issued
 **/
function all_userids()
{
  $users = survey_users();
  return array_keys($users);
}


