<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('admin/ajax/demo_markdown.php');

$md = stripslashes($_POST['markdown'] ?? '');
$html = render_demo_markdown($md);

$response = array( 'ok'=>true, 'rendered'=>$html );
$rval = json_encode($response);
echo $rval;
wp_die();
