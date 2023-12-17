<?php
namespace TLC\TTSurvey;

# __FILE__ = $WORDPRESS_DIR/wp-content/plugins/tlc-ttsurveys/admin/data_dump.php
$admin_dir = dirname(__FILE__);
$plugin_dir = dirname($admin_dir);
$plugins_dir = dirname($plugin_dir);
$content_dir = dirname($plugins_dir);
$wordpress_dir = dirname($content_dir);

require_once "$wordpress_dir/wp-load.php";

if(!defined('WPINC')) { wp_die(); }
if(!plugin_admin_can('data')) { wp_die('Unauthorized user'); }
if(!wp_verify_nonce($_GET['nonce'], DATA_NONCE)) { wp_die('Expired link'); }

require_once "$plugin_dir/include/users.php";
require_once "$plugin_dir/include/surveys.php";

$data = json_encode(
  array(
    'surveys'=>dump_all_survey_data(),
    'userids'=>User::dump_all_user_data(),
    'responses'=>array(),
  ),
  JSON_PRETTY_PRINT
);
$checksum = hash('crc32b',$data);

$data = "tlctt:$checksum\n$data";

if($_GET['pp']??false) {
  echo "<pre>$data</pre>";
} else {
  echo $data;
}

