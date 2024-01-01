<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/users.php');

log_info("Purging all survey and user data");

$posts = get_posts( array(
  'post_type' => SURVEY_POST_TYPE,
  'numberposts' => -1,
  'fields' => 'ids',
));

foreach($posts as $id) {
  log_info("Deleting survey post $id");
  wp_delete_post($id,true);
}

$posts = get_posts( array(
  'post_type' => USERID_POST_TYPE,
  'numberposts' => -1,
  'fields' => 'ids',
));

foreach($posts as $id) {
  log_info("Deleting user post $id");
  wp_delete_post($id,true);
}


$posts = get_posts( array(
  'post_type' => SURVEY_POST_TYPE,
  'post_status' => 'trash',
  'numberposts' => -1,
  'fields' => 'ids',
));

foreach($posts as $id) {
  log_info("Deleting survey post $id from trash");
  wp_delete_post($id,true);
}

$posts = get_posts( array(
  'post_type' => USERID_POST_TYPE,
  'post_status' => 'trash',
  'numberposts' => -1,
  'fields' => 'ids',
));

foreach($posts as $id) {
  log_info("Deleting user post $id from trash");
  wp_delete_post($id,true);
}

wp_send_json_success();
wp_die();
