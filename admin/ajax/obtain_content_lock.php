<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) {die;}

require_once plugin_path('admin/content_lock.php');

$lock = obtain_content_lock();

wp_send_json($lock);
wp_die();

