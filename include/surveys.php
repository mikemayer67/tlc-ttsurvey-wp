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

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');

/**
 * Register the custom post type
 **/

function register_survey_post_type()
{
  switch( survey_post_ui() )
  {
  case POST_UI_POSTS: $show_in_menu = 'edit.php';  break;
  case POST_UI_TOOLS: $show_in_menu = 'tools.php'; break;
  default:            $show_in_menu = false;       break;
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
 * Survey and Catalog classes
 **/

class Survey
{
  private $_post_id = null;
  private $_name = null;
  private $_status = null;
  private $_last_modified = null;
  private $_content = null;

  private function __construct($post)
  {
    if($post->post_type != SURVEY_POST_TYPE) {
      log_error("Attempted to construct survey from non-survey post ($post->ID)");
      wp_die();
    }

    $this->_post_id = $post_id = $post->ID;
    $this->_name = $name = $post->post_title;

    $status = get_post_meta($post->ID,'status') ?? null;
    if(!$status) {
      log_error("Survey $name is missing status metadata");
      wp_die();
    }
    if(count($status)>1) {
      log_error("Survey $name has multiple status entries in the metadata");
      wp_die();
    }
    $this->_status = $status[0];

    $this->_last_modified = get_post_modified_time('U',true,$post);
  }

  public static function from_name($name)
  {
    $posts = get_posts(
      array(
        'post_type' => SURVEY_POST_TYPE,
        'numberposts' => -1,
        'title' => $name,
      )
    );
    if(!$posts) {
      log_info("No survey found with name '$name'");
      return null;
    }
    if(count($posts)>1) {
      log_error("Multiple surveys found with name '$name'");
      wp_die();
    }

    return new Survey($posts[0]);
  }

  public static function from_post_id($post_id)
  {
    if(!$post_id) { return null; }
    return new Survey(get_post($post_id));
  }

  public static function from_post($post)
  {
    if(!$post) { return null; }
    return new Survey($post);
  }

  public static function create_new($name)
  {
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
      log_warning("Failed to insert new $name survey into posts");
      wp_die();
    }

    update_post_meta($post_id,'status',SURVEY_IS_DRAFT);
    update_post_meta($post_id,'responses',0);

    log_info("Created new $name survey");

    return Survey::from_post_id($post_id);
  }

  // getters

  public function post_id()       { return $this->_post_id;       }
  public function name()          { return $this->_name;          }
  public function status()        { return $this->_status;        }
  public function last_modified() { return $this->_last_modified; }

  // the following assumes that there is only one survey that is either draft or active
  public function is_draft()   { return $this->_status == SURVEY_IS_DRAFT;  }
  public function is_active()  { return $this->_status == SURVEY_IS_ACTIVE; }
  public function is_closed()  { return $this->_status == SURVEY_IS_CLOSED; }
  public function is_current() { return ! $this->is_closed();               }

  public function response_count()
  {
    return get_post_meta($this->_post_id,'responses') ?? 0;
  }

  public function content()
  {
    if(!$this->_content) {
      $post = get_post($this->_post_id);
      $json = $post->content;
      $this->_content = json_decode($json,true);
    }
    return $this->_content;
  }

  // state handling

  public function set_status($status) 
  {
    $this->_status = $status;
    update_post_meta($this->_post_id,'status',$status);
  }

  // update status from admin tabs

  public function update_content($survey,$sendmail)
  {
    $content = array();
    $content['survey'] = $survey;
    foreach( $sendmail as $key=>$template ) {
      $content['sendmail'][$key] = $template;
    }

    $rval = wp_update_post(
      array(
        'ID' => $this->_post_id,
        'post_content' => wp_slash(json_encode($content)),
      )
    );

    if(!$rval) 
    {
      log_warning("Failed to update content for $this->_name survey"); 
      return false;
    }

    wp_save_post_revision($this->post_id);

    log_info("Content updated for $this->_name survey");             
    $this->_content = $content;

    return true;
  }
}


class SurveyCatalog
{
  // catalog singleton
  private static $_instance = null;

  public static function instance()
  {
    if(!self::$_instance) { self::$_instance = new SurveyCatalog(); }
    return self::$_instance;
  }

  public static function close()
  {
    self::$_instance = null;
  }

  // catalog instance
  private $_index = array();
  private $_current = null;

  private function __construct()
  {
    $posts = get_posts(
      array(
        'post_type' => SURVEY_POST_TYPE,
        'numbrerposts' => -1,
      )
    );
    foreach( $posts as $post ) {
      $survey = Survey::from_post($post);
      $name = $survey->name();
      if( array_key_exists($name,$this->_index) ) {
        log_error("Multiple surveys found with name '$name'");
        wp_die();
      }
      $this->_index[$post->ID] = $survey;

      if($survey->is_current()) {
        if($this->_current) {
          log_error("Multiple open surveys found");
          wp_die();
        }
        $this->_current = $survey;
      }
    }
  }

  public function lookup_by_post_id($post_id)
  {
    return $this->_index[$post_id] ?? null;
  }

  public function current_survey() 
  { 
    return $this->_current; 
  }

  public function active_survey() 
  {
    if($this->_current && $this->_current->is_active()) { return $this->_current; }
    return null;
  }

  public function reopen_survey($post_id) 
  {
    $survey = $this->_index[$post_id] ?? null;
    if(!$survey) { return; }

    $new_name = $survey->name();
    if( $this->_current ) {
      $cur_name = $this->_current->name();
      if( $this->_current->post_id() == $survey->post_id() ) { return true; }

      if( $this->_current->status() == SURVEY_IS_ACTIVE ) {
        log_error("Attempting to reopen $new_name survey when $cur_name survey is already open");
      } else {
        log_error("Attempting to reopen $new_name survey when draft $cur_name survey exists");
      }
      return false;
    }

    $survey->set_status(SURVEY_IS_ACTIVE);
    $this->_current = $survey;

    return true;
  }

  public function create_new_survey($name)
  {
    if( $this->_current ) {
      $cur_name = $this->_current->name();
      log_error("Cannot create new survey with existing $cur_name survey open");
      wp_die();
    }
    $survey = Survey::create_new($name);
    if(!$survey) {
      log_warning("Failed to insert new $name survey into posts");
      wp_die();
    }
    $post_id = $survey->post_id();
    $this->index[$post_id] = $survey;
    return true;
  }

  public function closed_surveys($newest_to_oldest=true)
  {
    $post_ids = array_keys($this->_index);
    if($newest_to_oldest) { rsort($post_ids); }
    else                  {  sort($post_ids); }
    $closed = array();
    foreach($post_ids as $post_id) {
      $survey = $this->_index[$post_id];
      if($survey->is_closed()) { $closed[] = $survey; }
    }
    return $closed;
  }

  public function survey_names()
  {
    $names = array();
    foreach($this->_index as $survey) {
      $names[] = $survey->name();
    }
    return $names;
  }

  // update status from admin tabs

  public function update_status($status)
  {
    if(!$status) { return; false; }

    $current = $this->_current;
    if(!$current) { return false; }

    if($current->status() == $status) { return true; }

    $current->set_status($status);

    if($status == SURVEY_IS_CLOSED) { $this->_current = null; }
    return true;
  }
}

/**
 * Convenience functions
 **/

function survey_catalog() { return SurveyCatalog::instance();                   }
function current_survey() { return SurveyCatalog::instance()->current_survey(); }
function active_survey()  { return SurveyCatalog::instance()->active_survey();  }
function closed_surveys() { return SurveyCatalog::instance()->closed_surveys(); }

function reopen_survey($post_id) { return SurveyCatalog::instance()->reopen_survey($post_id); }
function create_new_survey($name) { return SurveyCatalog::instance()->create_new_survey($name); }

/**
 * Updates from admin tab
 **/

function update_survey_status_from_post()
{
  $status = $_POST['survey_status'] ?? null;
  $catalog = SurveyCatalog::instance();
  return $catalog->update_status($status);
}

function update_survey_content_from_post()
{
  $pid = $_POST['pid'] ?? '';
  $survey = $_POST['survey'] ?? '';
  $sendmail = array();
  foreach( array_keys(SENDMAIL_TEMPLATES) as $key ) {
    $sendmail[$key] = $_POST[$key] ?? '';
  }

  $catalog = SurveyCatalog::instance();
  $current = $catalog->current_survey();
  if(!$current) { 
    log_warning("Attempted to update survey with no current survey");
    return false; 
  }

  $cur_pid = $current->post_id();
  if(strcmp($pid,$cur_pid)) {
    log_warning("Attempted to update survey $pid, current is $cur_pid");
    return false;
  }

  return $current->update_content($survey,$sendmail);
}

/**
 * Data Dump/Load
 **/

function dump_all_survey_data()
{
  $posts = get_posts(
    array(
      'post_type' => SURVEY_POST_TYPE,
      'numberposts' => -1,
    )
  );

  $data = array();
  foreach($posts as $post)
  {
    $content = json_decode($post->post_content,true);
    $id = $post->ID;
    $data[] = array(
      'name' => $post->post_title,
      'post_id' => $post->ID,
      'content' => $content,
      'responses' => get_post_meta($id,'responses')[0] ?? 0,
      'status' => get_post_meta($id,'status')[0] ?? '',
    );
  }
  return $data;
}

/**
 * parse survey yaml into json
 **/

function parse_survey_yaml($yaml, &$error=null)
{
  return "Need to implement this";
}

