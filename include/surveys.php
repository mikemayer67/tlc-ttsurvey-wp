<?php
namespace TLC\TTSurvey;

/**
 * Setup and querying of plugin database tables
 */

if( ! defined('WPINC') ) { die; }

/**
 * The survey content information is stored as an entry as wordpress posts
 * using a custome post type of tlc-ttsurvey-form.  Each post corresponds
 * to a single survey.
 *
 * The post title contains the name of the survey
 * The post content contains a json encoded array containing all of the 
 * survey content
 *
 * NOTE... while the intent of the name is to indicate the year the
 *   survey is conducted, it can actually be any valid string.  This
 *   means that it can be more flexible, e.g there could be both a
 *   "2023" and "2023b" survey or even something like "2022-2023" 
 *
 * Post metadata is used to provide additional information about each
 * survey's status
 *   - state: draft, active, closed
 *   - responses: number of submitted survey forms
 **/

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');

const SURVEY_IS_DRAFT = 'draft';
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

function get_survey_post_id($name)
{
  log_dev("get_survey_post_id($name)");
  $ids = get_posts(
    array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
      'title' => $name,
      'fields' => 'ids',
    )
  );
  if(count($ids) > 1) {
    # log error both to the plugin log and to the php error log
    log_error("Multiple posts associated with $name survey");
    wp_die();
  }
  if(!$ids) { 
    log_info("No post found for name '$name'");
    return null;
  }
  return $ids[0];
}

function get_survey_post_by_name($name)
{
  log_dev("get_survey_post_by_name($name)");
  $post_id = get_survey_post_id($name);
  return get_survey_post_by_id($post_id);
}

function get_survey_post_by_id($post_id)
{
  log_dev("get_survey_post_by_id($post_id)");
  if($post_id) {
    return get_post($post_id);
  } else {
    return null;
  }
}

function survey_catalog()
{
  $posts = get_posts(
    array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
    )
  );
  $rval = array();
  foreach( $posts as $post ) {
    $name = $post->post_title;
    $post_id = $post->ID;
    if( array_key_exists($name,$rval) ) {
      log_error("Multiple posts associated with name '$name'");
      wp_die();
    }

    $status = get_post_meta($post_id,'status') ?? null;
    if($status) {
      if(count($status) > 1) {
        log_error("Multiple statuses associated with $name survey");
        wp_die();
      }
      $status = $status[0];
    }
    $rval[$post_id] = array('post_id'=>$post_id, 'name'=>$name, 'status'=>$status);
  }

  return $rval;
}

function current_survey()
{
  $rval = null;
  $catalog = survey_catalog();
  foreach($catalog as $post_id=>$survey) {
    if( in_array($survey['status'],[SURVEY_IS_ACTIVE,SURVEY_IS_DRAFT]) )
    {
      if($rval) {
        error_log("Multiple active/draft surveys found");
        wp_die();
      }
      $rval = $info;
    }
  }
  return $rval;
}

function survey_response_count($post_id)
{
  return get_post_meta($post_id,'responses') ?? 0;
}

function survey_form($post_id)
{
  log_dev("survey_form($post_id)");
  if(!$post_id) { return null; }
  $post = get_post($post_id);
  return $post->content;
}

/**
 * Update status from settings
 **/

// function update_status_from_post()
// {
//   log_dev("update_settings_from_post");
// 
//   $current = current_survey();
//   if($current_survey) {
//     [$current_year,$current_status] = $current;
//   } else {
//     $current_year = date('Y');
//     $current_status = survey_years()[$current_year] ?? null;
//   }
// 
//   $new_status = $_POST['survey_status'] ?? null;
//   if($new_status) {
//     log_dev("new status: $new_status");
//     if($new_status != $current_status) {
//       log_dev("change status");
//       $post_id = get_survey_post_id($current_year);
//       if($post_id) {
//         update_post_meta($post_id,'status',$new_status);
//       } else {
//         log_error("Cannot find survey for $year");
//       }
//     }
//   }
// }
