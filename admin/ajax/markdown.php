<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/markdown.php');

function demo_render_sendmail_markdown($md)
{
  return render_sendmail_markdown(
    $md,
    "Thomas Smith", // name
    "tsmith59", // userid
    "t.smith@tmail.net", // email
    "demo_token", // token
  );
}

