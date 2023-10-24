<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('external/parsedown/Parsedown.php');

function render_markdown($md,$placeholders=array())
{
  foreach($placeholders as $key=>$value) {
    $md = str_replace("<<$key>>",$value,$md);
  }

  $pd = new \Parsedown();
  $html = $pd->text($md);
  return "<div>$html</div>";
}

