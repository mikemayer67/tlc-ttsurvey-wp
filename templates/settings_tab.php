<?php
namespace TLC\TTSurvey;

require_once plugin_path('settings.php');

$settings = Settings::instance();

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
    <div class=label>Nothing yet to set</div>
  </div>
  <input type="submit" value="Save" class="submit button button-primary button-large">
</form>
