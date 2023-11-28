<?php
namespace TLC\TTSurvey;

# __FILE__ = $WORDPRESS_DIR/wp-content/plugins/tlc-ttsurveys/admin/data_view.php
$admin_dir = dirname(__FILE__);
$plugin_dir = dirname($admin_dir);
$plugins_dir = dirname($plugin_dir);
$content_dir = dirname($plugins_dir);
$wordpress_dir = dirname($content_dir);

require_once "$wordpress_dir/wp-load.php";

if(!defined('WPINC')) { wp_die(); }
if(!plugin_admin_can('data')) { wp_die('Unauthorized user'); }
if(!wp_verify_nonce($_GET['nonce'], DATA_NONCE)) { wp_die('Expired link'); }

/**
 * Survey Posts & Revisions
 **/

$survey_posts = get_posts(
  array(
    'post_type' => SURVEY_POST_TYPE,
    'numberposts' => -1,
  )
);
$surveys = array();
foreach( $survey_posts as $survey_post ) 
{
  $id = $survey_post->ID;
  $surveys[$id] = array(
    'title' => $survey_post->post_title,
    'name' => $survey_post->post_name,
    'created' => $survey_post->post_date_gmt,
    'modified' => $survey_post->post_modified_gmt,
    'content' => json_decode($survey_post->post_content,true),
    'responses' => get_post_meta($id,'responses')[0] ?? 0,
    'status' => get_post_meta($id,'status')[0] ?? '',
  );
}

$userid_posts = get_posts(
  array(
    'post_type' => USERID_POST_TYPE,
    'numberposts' => -1,
  )
);
$userids = array();
foreach( $userid_posts as $userid_post ) 
{
  $id = $userid_post->ID;
  $userids[$id] = array(
    'title' => $userid_post->post_title,
    'name' => $userid_post->post_name,
    'created' => $userid_post->post_date_gmt,
    'modified' => $userid_post->post_modified_gmt,
    'content' => json_decode($userid_post->post_content,true),
    'anonid' => get_post_meta($id,'anonid')[0] ?? '',
  );
}


$data = json_encode(array(
  'surveys'=>$surveys,
  'userids'=>$userids,
));

print_r($data);
wp_die();
