<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

require_once plugin_path('settings.php');
require_once plugin_path('logger.php');

$title = esc_html(get_admin_page_title());

$settings = Settings::instance();
$all_users = get_users();

if(($_POST["action"] ?? null) == "update") {
  /* nonce is checked within the update_from_post method */
  $settings->update_from_post($_POST);
}

$nonce = wp_nonce_field(SETTINGS_NONCE);

$action = $_SERVER['SCRIPT_URI'].'?'.http_build_query(array(
  'page'=>SETTINGS_PAGE_SLUG,
));

$current_year = date('Y');

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
  <div class=label>Active Year</div>
  <select name='active_year' class='tlc settings'>
  <option value='current'><?=$current_year?></option>
  </select>
  </div>
  <input type="submit" value="Save" class="submit button button-primary button-large">
</form>

</div>

