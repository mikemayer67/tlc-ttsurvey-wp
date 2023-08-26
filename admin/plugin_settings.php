<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

require_once plugin_path('settings.php');

$settings = Settings::instance();

$active_year = $settings->get(ACTIVE_YEAR_KEY);
$current_year = date('Y');
$survey_years = survey_years();
$survey_years[] = date('Y');
$survey_years = array_unique($survey_years);
arsort($survey_years);

$nonce = wp_nonce_field(SETTINGS_NONCE);

$action = $_SERVER['SCRIPT_URI'].'?'.http_build_query(array(
  'page'=>SETTINGS_PAGE_SLUG,
  'tab'=>'overview',
));

?>

<form id='tlc-ttsurvey-settings' class='tlc' action='<?=$action?>' method="POST">
  <input type="hidden" name="action" value="update">
  <?=$nonce?>
  <div class=tlc>

  <div class=label>Active Year</div>
  <select name='active_year' class='tlc settings'>
<?php foreach( $survey_years as $year ) { ?>
  <option value=<?=$year?> <?php echo($year==$active_year ? "selected" : ""); ?>><?=$year?></option>
<?php } ?>
  </select>

  <div class=label>Survey Admins</div>
  <table id='tlc-ttsurvey-admin-caps' class='tlc settings'>
  <tr>
    <th></th>
    <th>Responses</th>
    <th>Structure</th>
  </tr>
<?php
$caps = $settings->get(CAPS_KEY);
$all_users = get_users();
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
<?php } ?>
  </table>

<?php
  $pdf_uri = $settings->get(PDF_URI_KEY);
?>
  <div class=label>Survey Download URL</div>
  <div class=info>Location for a downloadable copy of the survey</div>
  <input type='URL' class='tlc settings' size=50 name='pdf_uri' value='<?=$pdf_uri?>'
   pattern='^(http|https|ftp|ftps)://[a-zA-Z].*$'>

  </div>

  <input type="submit" value="Save" class="submit button button-primary button-large">
</form>
