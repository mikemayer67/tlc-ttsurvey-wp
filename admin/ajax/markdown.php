<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/markdown.php');

function render_demo_markdown($md)
{
  return render_markdown(
    $md,
    array(
      'name' => 'Thomas Smith',
      'userid' => 'tsmith59',
      'email' => 't.smith&tttmail.net',
      'token' => 'demo_token',
      'survey' => 'Demo',
    )
  );
}

