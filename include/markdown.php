<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('external/parsedown/Parsedown.php');

function render_markdown($md)
{
  $pd = new \Parsedown();
  $html = $pd->text($md);
  return "<div>$html</div>";
}

function render_sendmail_markdown($md,$name,$userid,$email,$token)
{
  $md = str_replace('<<name>>',$name,$md);
  $md = str_replace('<<userid>>',$userid,$md);
  $md = str_replace('<<email>>',$email,$md);
  $md = str_replace('<<token>>',$token,$md);
  return render_markdown($md);
}

