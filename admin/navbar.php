<?php
namespace TLC\TTSurvey;

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

$title = esc_html(get_admin_page_title());

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

$status = $_GET['status'] ?? null;
if($status) {
  $status = "<span class='tlc-status info'>$status</span>";
} else {
  $status = "<span class='tlc-status' style='display:none;'></span>";
}
 
echo "<div id='tlc-ttsurvey-admin'>";
echo "<h1>$title$status</h1>";
echo "<div class='nav-tab-wrapper'>";

$tabs = [
  ['overview','Overview'],
];

if(plugin_admin_can('manage')) { $tabs[] = ['settings','Settings']; }
if(plugin_admin_can('content')) { $tabs[] = ['content','Content']; }
if(plugin_admin_can('responses')) { $tabs[] = ['responses','Responses']; }

$cur_tab = $_GET['tab'] ?? 'overview';

$query_args = array();
$uri_path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$query_args);
if($cur_tab != 'content') {
  unset($query_args['sid']);
}
foreach($tabs as [$tab,$label]) {
  $class = 'nav-tab';
  if($cur_tab == $tab) {
    $class .= ' nav-tab-active';
  }
  $query_args['tab'] = $tab;
  unset($query_args['status']);
  $uri = $uri_path . '?' .http_build_query($query_args);
  echo "<a class='$class' href='$uri'>$label</a>";
}

echo "</div>"; // nav-tab-wrapper

require plugin_path("admin/survey_${cur_tab}.php");

echo "</div>"; // tlc-ttsurvey-admin

