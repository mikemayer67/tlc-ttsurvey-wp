<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }
$icon = plugin_url('img/icons8-error.png');
?>

<div class="w3-container">
  <div class="w3-card w3-pale-yellow w3-leftbar w3-border-orange" style="width:50%">
      <img class='w3-center w3-margin' src="<?=$icon?>" width=64 height=64>
      <span class='w3-xlarge w3-margin'><b>Invalid Survey Page</b></span>
  </div>
</div>
