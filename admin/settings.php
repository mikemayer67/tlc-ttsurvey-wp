<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

require_once plugin_path('settings.php');
require_once plugin_path('logger.php');

$title = esc_html(get_admin_page_title());

$settings = Settings::instance();
$all_users = get_users();

if(($_POST["action"] ?? null) == "update") {
  if (!wp_verify_nonce($_POST['_wpnonce'],SETTINGS_NONCE)) {
    log_error("failed to validate nonce");
    wp_die("Bad nonce");
  }

  $settings->set('caps',$_POST['caps']);

  foreach($all_users as $user) {
    $id = $user->id;
    foreach(['responses','structure'] as $cap) {
      $key = "tlc-ttsurvey-$cap";
      if($_POST['caps'][$cap][$id]) {
        $user->add_cap($key);
      } else {
        $user->remove_cap($key);
      }
    }
  }

  log_info(print_r($settings,true));
}

$nonce = wp_nonce_field(SETTINGS_NONCE);

$action = $_SERVER['SCRIPT_URI'].'?'.http_build_query(array(
  'page'=>SETTINGS_PAGE_SLUG,
));

?>

<div class=wrap>
<h1><?=$title?></h1>

<form id='tlc-ttsurvey-settings' class='tlc' action='<?=$action?>' method="POST">
  <input type="hidden" name="action" value="update">
  <?=$nonce?>
  <div class=tlc>
  <div class=label>Survey Admins</div>
  <table id='tlc-ttsurvey-admin-caps' class='tlc settings'>
  <tr>
    <th></th>
    <th>Responses</th>
    <th>Structure</th>
  </tr>
<?php
$caps = $settings->get('caps');
foreach($all_users as $user) {
  $id = $user->id;
  $name = $user->display_name;
  $response = $caps['responses'][$id] ? "checked" : "";
  $structure = $caps['structure'][$id] ? "checked" : "";
?>
  <tr>
    <td class=name><?=$name?></td>
    <td><div class=cap>
    <input type=checkbox value=1 name="caps[responses][<?=$id?>]" <?=$response?>>
    </div></td>
    <td><div class=cap>
    <input type=checkbox value=1 name="caps[structure][<?=$id?>]" <?=$structure?>>
    </div></td>
  </tr>
<?php
}
?>
  </table>
  <ul>
  </ul>
  </div>
  <input type="submit" value="Save" class="submit button button-primary button-large">
</form>

</div>

