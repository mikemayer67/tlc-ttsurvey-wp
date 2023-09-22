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

function survey_deactivate()
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
    error_log("Multiple posts associated with year $year");
    die;
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

function current_survey_status()
{
  $current_year = date('Y');
  $post_id = get_survey_post_id($current_year);
  if(!$post_id) { return null; }
  $status = get_post_meta($post_id,'status')[0] ?? null;
  return $status;
}

function active_survey_year()
{
  $query = array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
      'meta_key' => 'status',
      'meta_value' => SURVEY_IS_ACTIVE,
      'orderby' => 'title',
    );
  $posts = get_posts($query);
  if($posts) {
    $post = end($posts);
    return end($posts)->post_title;
  }
  return null;
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
      error_log("Multiple posts associated with year $year");
      die;
    }

    $status = get_post_meta($post->ID,'status') ?? null;
    if($status) {
      if(count($status) > 1) {
        log_error("Multiple status associated $year with survey");
        error_log("Multiple status associated $year with survey");
        die;
      }
      $status = $status[0];
    }
    $rval[$year] = $status;
  }

  $current_year = date('Y');
  if(!array_key_exists($current_year,$rval)) {
    $rval[$current_year] = null;
  }

  return $rval;
}

function survey_form($year)
{
  log_dev("survey_form($year)");
  $post_id = get_survey_post_id($id);
  if(!$post_id) { return null; }
  $post = get_post($post_id);
  return $post->content;
}


