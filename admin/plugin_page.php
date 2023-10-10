<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

$title = esc_html(get_admin_page_title());
$status = "";

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

if(wp_verify_nonce($_POST['_wpnonce'],OPTIONS_NONCE)) {
  $action = $_POST['action'] ?? null;
  if($action == "update") 
  {
    update_options_from_post();
    update_survey_status_from_post();
    $status = "<span class='tlc-status'>updated</span>";
  }
  elseif($action == "clear-log") 
  {
    clear_logger();
  }
  elseif($action == "new-survey")
  {
    create_new_survey($_POST['name']);
  }
  elseif($action == "reopen-survey")
  {
    reopen_survey($_POST['pid']);
  }
  elseif($action == 'update-survey')
  {
    if(update_survey_content_from_post()) {
      $status = "<span class='tlc-status'>updated</span>";
    } else {
      $status = "<span class='tlc-status error'>failed to update</span>";
    }
  }
}

echo "<h1>$title$status</h1>";
echo "<div class='nav-tab-wrapper'>";

$tabs = [
  ['overview','Overview'],
  ['settings','Settings'],
];

if(current_user_can('tlc-ttsurvey-content')) {
  $tabs[] = ['content','Content'];
}
if(current_user_can('tlc-ttsurvey-responses')) {
  $tabs[] = ['responses','Responses'];
}

$tabs[] = ['log','Log'];

$cur_tab = $_GET['tab'] ?? 'overview';

$query_args = array();
$uri_path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$query_args);
if($cur_tab != 'content') {
  unset($query_args['sid']);
}
foreach($tabs as $tab) {
  $class = 'nav-tab';
  $tab_label = $tab[1];
  if($cur_tab == $tab[0]) {
    $class .= ' nav-tab-active';
  }
  $query_args['tab'] = $tab[0];
  $uri = $uri_path . '?' .http_build_query($query_args);
  echo "<a class='$class' href='$uri'>$tab_label</a>";
}

echo "</div>";

require plugin_path("admin/survey_${cur_tab}.php");

