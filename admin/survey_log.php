<?php
namespace TLC\TTSurvey;

if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');

$action = $_SERVER['REQUEST_URI'];
?>

<div class='tlc-log'>
<?php dump_log_to_html("DEV"); ?>
</div>

<form id='tlc-clear-log' class='tlc' action='<?=$action?>' method="POST">
  <input type='hidden' name='action' value='clear-log'>
  <?php wp_nonce_field(OPTIONS_NONCE); ?>
  <input type='submit' value='Clear Log' class='submit button button-primary button-large'>
</form>
<a href='<?=plugin_url(PLUGIN_LOG_FILE)?>' target='_blank'>
View Log File
</a>

