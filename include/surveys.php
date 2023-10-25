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
 * NOTE... while the intent of the name/title is to indicate the year the
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
require_once plugin_path('include/sendmail.php');

const SURVEY_IS_DRAFT = 'draft';
const SURVEY_IS_ACTIVE = 'active';
const SURVEY_IS_CLOSED = 'closed';

const POST_UI_NONE = 'NONE';
const POST_UI_POSTS = 'POSTS';
const POST_UI_TOOLS = 'TOOLS';
const POST_UI_ = array(
  'NONE' => "Disabled",
  'POSTS' => "Posts menu",
  'TOOLS' => "Tools menu",
);

/**
 * Register the custom post type
 **/

const SURVEY_POST_TYPE = 'tlc-ttsurvey-form';

function register_survey_post_type()
{
  switch( survey_post_ui() )
  {
  case POST_UI_POSTS:
    $show_in_menu = 'edit.php';
    break;
  case POST_UI_TOOLS:
    $show_in_menu = 'tools.php';
    break;
  default:
    $show_in_menu = false;
    break;
  }
  register_post_type( SURVEY_POST_TYPE,
    array(
      'labels' => array(
        'name' => 'Surveys',
        'menu_name' => "Time & Talent Surveys",
        'singular_name' => 'Survey',
        'add_new' => 'New Survey',
        'add_new_item' => 'Add New Survey',
        'edit_item' => 'Edit Survey',
        'new_item' => 'New Survey',
        'view_item' => 'View Survey',
        'search_items' => 'Search Surveys',
        'not_found' =>  'No Surveys Found',
        'not_found_in_trash' => 'No Surveys found in Trash',
      ),
      'has_archive' => false,
      'supports' => array('title','editor','revisions'),
      'public' => false,
      'show_ui' => true,
      'show_in_rest' => false,
      'show_in_menu' => $show_in_menu,
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

function survey_edit_form_top($post)
{
  $type = $post->post_type;
  if($post->post_type == SURVEY_POST_TYPE) {
    $content_url = admin_url() . "admin.php?page=" . SETTINGS_PAGE_SLUG;
    echo "<p class='tlc-post-warning'>";
    echo "Be very careful editing this data.<br>";
    echo "The JSON formatting must be preserved to avoid breaking the survey form.";
    echo "</p>";
    echo "<p class='tlc-post-info'>";
    echo "This post editor is provided to manage revisions and to make <b>very</b> minor edits to the form.<br>";
    echo "The form content should be modified in the Content tab of the ";
    echo "<a href='$content_url'>Time andd Talent admin page</a>.";
    echo "</p>";
  }
}

function survey_revisions_to_keep($num)
{
  return 15;
}

add_action('init',ns('surveys_init'));
add_action('edit_form_top',ns('survey_edit_form_top'));
add_action('wp_'.SURVEY_POST_TYPE.'_revisions_to_keep',ns('survey_revisions_to_keep'));

/**
 * Survey lookup functions
 **/

function get_survey_post_id($name)
{
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
  $post_id = get_survey_post_id($name);
  return get_survey_post_by_id($post_id);
}

function get_survey_post_by_id($post_id)
{
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
    $rval[$post_id] = array(
      'post_id'=>$post_id, 
      'name'=>$name, 
      'status'=>$status,
      'last_modified'=>get_post_modified_time('U',true,$post),
    );
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
      $rval = $survey;
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
  if(!$post_id) { return null; }
  $post = get_post($post_id);
  return $post->content;
}

function reopen_survey($post_id)
{
  $current = current_survey();
  if($current) {
    $name = $current['name'];
    $status = $current['status'];
    if($status == SURVEY_IS_ACTIVE) {
      log_error("Attempted to reopen survey when $name is already open");
    } else {
      log_error("Attempted to reopen survey when draft $name exists");
    }
    return null;
  }
  $post = get_post($post_id);
  if(!$post) {
    log_error("Attempted to reopen nonexistent survey ($post_id)");
    return null;
  }
  if($post->post_type != SURVEY_POST_TYPE) {
    log_error("Post $post_id is not a survey post");
    return null;
  }

  update_post_meta($post_id,'status',SURVEY_IS_ACTIVE);

  return true;
}

function create_new_survey($name)
{
  $current = current_survey();
  if($current) {
    $name = $current['name'];
    $status = $current['status'];
    if($status == SURVEY_IS_ACTIVE) {
      log_error("Attempted to create survey when $name is already open");
    } else {
      log_error("Attempted to create survey when draft $name exists");
    }
    return null;
  }

  $post_id = wp_insert_post(
    array(
      'post_content' => '',
      'post_title' => $name,
      'post_type' => SURVEY_POST_TYPE,
      'post_status' => 'publish',
    ),
    true,
  );
  if(!$post_id) {
    log_warning("Failed to insert new survey into wp_posts");
    return null;
  }

  update_post_meta($post_id,'status',SURVEY_IS_DRAFT);
  update_post_meta($post_id,'responses',0);
}


/**
 * Update survey status from admin settings tab
 **/

function update_survey_status_from_post()
{
  $current = current_survey();
  if(!$current) { return null; }

  $new_status = $_POST['survey_status'] ?? null;
  if(!$new_status) { return null; }

  update_post_meta($current['post_id'],'status',$new_status);
}

/**
 * Update survey content from admin content tab
 **/

function update_survey_content_from_post()
{
  $current = current_survey();
  if(!$current) { 
    log_warning("Attempted to update survey with no current survey");
    return null; 
  }

  $pid = $_POST['pid'] ?? '';
  $cur_pid = $current['post_id'];

  log_dev("pid:$pid, cur_pid:$cur_pid");

  if(strcmp($pid,$cur_pid)!=0) {
    log_warning("Attempted to update survey $pid, current is $cur_pid");
    return null;
  }

  $survey = $_POST['survey'] ?? '';
  $data = array('survey' => $survey);
  foreach( array_keys(SENDMAIL_TEMPLATES) as $key ) {
    $data[$key] = $_POST[$key] ?? '';
  }

  log_dev("data: ".print_r($data,true));

  $rval = wp_update_post(array(
    'ID' => $pid,
    'post_content' => wp_slash(json_encode($data)),
  ));

  wp_save_post_revision($pid);

  log_dev("rval: $rval");

  if($rval) {
    $name = $current['name'];
    log_info("Content updated for $name survey");
  } else {
    log_warning("Failed to update content for survey $pid");
  }

  return $rval;
}


/**
 * parse survey yaml into json
 **/

function parse_survey_yaml($yaml, &$error=null)
{
  return "Need to implement this";
}

