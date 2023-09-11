<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

$title = esc_html(get_admin_page_title());
$status = "";

require_once plugin_path('settings.php');
require_once plugin_path('logger.php');

$action = $_POST['action'] ?? null;
if($action == "update") 
{
  /* nonce is checked within the update_from_post method */
  update_options_from_post();
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

echo "<h1>$title$status</h1>";
echo "<div class='nav-tab-wrapper'>";

$tabs = [
  ['overview','Overview'],
  ['settings','Settings'],
];

if(current_user_can('tlc-ttsurvey-structure')) {
  $tabs[] = ['structure','Structure'];
}
if(current_user_can('tlc-ttsurvey-responses')) {
  $tabs[] = ['responses','Responses'];
}

$tabs[] = ['log','Log'];

$cur_tab = $_GET['tab'] ?? 'overview';

foreach($tabs as $tab) {
  $class = 'nav-tab';
  $tab_key = $tab[0];
  $tab_label = $tab[1];
  if($cur_tab == $tab_key) {
    $class .= ' nav-tab-active';
  }
  $uri = "{$_SERVER['REQUEST_URI']}&tab={$tab_key}";
  echo "<a class='$class' href='$uri'>$tab_label</a>";
}

echo "</div>";

require plugin_path("admin/plugin_${cur_tab}.php");

