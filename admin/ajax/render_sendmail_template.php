<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

log_dev("render_sendmail_template: ".print_r($_POST,true));

require_once plugin_path('include/logger.php');
require_once plugin_path('include/markdown.php');

$md = stripslashes($_POST['markdown'] ?? '');
$html = render_sendmail_markdown($md);

$response = array( 'ok'=>true, 'rendered'=>$html );
$rval = json_encode($response);
echo $rval;
wp_die();
