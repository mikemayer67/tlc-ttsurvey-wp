<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

$title = esc_html(get_admin_page_title());
$status = "";

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');

$action = $_POST['action'] ?? null;
if($action == "update") 
{
  /* nonce is checked within the update_from_post method */
  update_options_from_post();
  update_survey_from_post();
  $status = "<span class='tlc-status'>udpated</span>";
}
elseif($action == "clear-log") 
{
  if (!wp_verify_nonce($_POST['_wpnonce'],OPTIONS_NONCE)) 
  {
    log_error("failed to validate nonce");
    wp_die("Bad nonce");
  }
  clear_logger();
}
elseif($action == "start-survey")
{
  $option = $_POST['option'] ?? null;
  $m = array();
  if($option == "create")
  {
    $name = $_POST['name'];
    log_info("Create new survey with name $name");
    create_new_survey($name);
  }
  elseif(preg_match('/^reopen-(\d+)$/',$option,$m))
  {
    $post_id = $m[1];
    log_info("Reopen survey with post_id $post_id");
    reopen_survey($post_id);
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

