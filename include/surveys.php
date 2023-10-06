<?php
namespace TLC\TTSurvey;

/**
 * Setup and querying of plugin database tables
 */

if( ! defined('WPINC') ) { die; }

/**
 * The survey content information is stored as an entry as wordpress posts
 * using a custome post type of tlc-ttsurvey-form.  Each post corresponds
 * to a single survey year.
 *
 * The post title contains the year of the survey
 * The post content contains a json encoded array containing all of the 
 * survey content
 *
 * NOTE... while the intent of the year is to indicate the year the
 *   survey is conducted, it can actually be any valid string.  This
 *   means that it can be more flexible, e.g there could be both a
 *   "2023" and "2023b" survey or even something like "2022-2023" 
 *
 * Post metadata is used to provide additional information about each
 * year's survey status
 *   - state: pending, active, closed
 **/

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');

const SURVEY_IS_PENDING = 'pending';
const SURVEY_IS_ACTIVE = 'active';
const SURVEY_IS_CLOSED = 'closed';

/**
 * Register the custom post type
 **/

const SURVEY_POST_TYPE = 'tlc-ttsurvey-form';

function register_survey_post_type()
{
  register_post_type( SURVEY_POST_TYPE,
    array(
      'labels' => array(
        'name' => 'TLC TTSurvey Forms',
        'singular_name' => 'Form',
        'add_new' => 'New Form',
        'add_new_item' => 'Add New Form',
        'edit_item' => 'Edit Form',
        'new_item' => 'New Form',
        'view_item' => 'View Form',
        'search_items' => 'Search Forms',
        'not_found' =>  'No Forms Found',
        'not_found_in_trash' => 'No Forms found in Trash',
      ),
      'has_archive' => false,
      'public' => false,
      'show_ui' => true,
      'show_in_rest' => false,
      'show_in_menu' => false,
    ),
  );
}

function surveys_init()
{
  register_survey_post_type();
}

function surveys_activate()
{
  log_info("Surveys Activate");
  register_survey_post_type();
  flush_rewrite_rules();
}

function surveys_deactivate()
{
  log_info("Surveys Deactivate");
  unregister_post_type(SURVEY_POST_TYPE);
}

add_action('init',ns('surveys_init'));

/**
 * Survey lookup functions
 **/

function get_survey_post_id($year)
{
  log_dev("get_survey_post_id($year)");
  $ids = get_posts(
    array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
      'title' => $year,
      'fields' => 'ids',
    )
  );
  if(count($ids) > 1) {
    # log error both to the plugin log and to the php error log
    log_error("Multiple posts associated with year $year");
    wp_die();
  }
  if(!$ids) { 
    log_info("No post found for year $year");
    return null;
  }
  return $ids[0];
}

function get_survey_post($year)
{
  log_dev("get_survey_post($year)");
  $post_id = get_survey_post_id($year);
  if($post_id) {
    return get_post($post_id);
  } else {
    return null;
  }
}

function survey_years()
{
  $posts = get_posts(
    array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
    )
  );
  $rval = array();
  foreach( $posts as $post ) {
    $year = $post->post_title;
    if( array_key_exists($year,$rval) ) {
      # log error both to the plugin log and to the php error log
      log_error("Multiple posts associated with year $year");
      wp_die();
    }

    $status = get_post_meta($post->ID,'status') ?? null;
    if($status) {
      if(count($status) > 1) {
        log_error("Multiple status associated $year with survey");
        wp_die();
      }
      $status = $status[0];
    }
    $rval[$year] = $status;
  }

  return $rval;
}

function current_survey()
{
  $years = survey_years();

  $summary = array();
  foreach($years as $year=>$status) {
    if(!key_exists($status,$summary)) {
      $summary[$status] = array();
    }
    $summary[$status][] = $year;
  }

  foreach([SURVEY_IS_ACTIVE,SURVEY_IS_PENDING] as $status)
  {
    if(key_exists($status,$summary))
    {
      $years = $summary[$status];
      if(count($years) > 1) {
        error_log("Too many $status surveys");
        wp_die();
      }
      return array($years[0],$status);
    }
  }

  return null;
}

function reopen_closed_survey($year)
{
  $current = current_survey();
  if($current) {
    log_error("$current[0] already $current[1]");
    return null;
  }
  $status = survey_years();
  if(!key_exists($year,$status)) {
    log_error("No survey found for $year");
    return null;
  }
  if($status[$year] != SURVEY_IS_CLOSED) {
    log_error("$year is not currently closed");
    return null;
  }

  $post_id = get_survey_post_id($year);
  if(!$post_id) {
    log_error("Cannot find survey for $year");
    return null;
  }

  update_post_meta($post_id,'status',SURVEY_IS_ACTIVE);
  return true;
}

function return_current_survey_to_pending()
{
  $current = current_survey();
  if(!$current) {
    log_error("No current survey found");
    return null;
  }
  [$year,$status] = $current;
  if($status!=SURVEY_IS_ACTIVE) {
    log_error("Current survey is already pending");
    return null;
  }
  $post_id = get_survey_post_id($year);
  if(!$post_id) {
    log_error("Cannot find survey for $year");
    return null;
  }
  update_post_meta($post_id,'status',SURVEY_IS_PENDING);
  return true;
}

function activate_current_survey()
{
  $current = current_survey();
  if(!$current) {
    log_error("No current survey found");
    return null;
  }
  [$year,$status] = $current;
  if($status != SURVEY_IS_PENDING) {
    log_error("Current survey is already active");
    return null;
  }
  $post_id = get_survey_post_id($year);
  if(!$post_id) {
    log_error("Cannot find survey for $year");
    return null;
  }
  update_post_meta($post_id,'status',SURVEY_IS_ACTIVE);
  return true;
}

function close_current_survey()
{
  $current = current_survey();
  if(!$current) {
    log_error("No current survey found");
    return null;
  }
  [$year,$status] = $current;
  $post_id = get_survey_post_id($year);
  if(!$post_id) {
    log_error("Cannot find survey for $year");
    return null;
  }
  update_post_meta($post_id,'status',SURVEY_IS_CLOSED);
  return true;
}

function survey_form($year)
{
  log_dev("survey_form($year)");
  $post_id = get_survey_post_id($id);
  if(!$post_id) { return null; }
  $post = get_post($post_id);
  return $post->content;
}


