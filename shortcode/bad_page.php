<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }
$icon = plugin_url('img/icons8-error.png');
?>

<div id='status-message' class="warning card">
  <img src="<?=$icon?>" width=64 height=64>
  <span>Invalid Survey Page</span>
</div>
