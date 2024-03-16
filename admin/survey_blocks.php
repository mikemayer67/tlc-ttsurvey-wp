<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }
if( !plugin_admin_can('content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/logger.php');

function gen_survey_sections($survey)
{
  $content = $survey->content();
  $content = $content['survey'] ?? [];
  $sections = [];
  foreach( $content as $section ) {
    $sections[] = gen_survey_section($section);
  }
  return $sections;
}

function gen_survey_section($section)
{
  $name = $section['name'];
  $tgt = str_replace(' ','-',strtolower($name));

  $tab = "<li class='nav-tab section $tgt' data-target='$tgt'>$name</li>";

  $body = "<div class='section $tgt'>";
  $body .= "$name stuff goes here";
  $body .= "</div>";

  return ['tab'=>$tab, 'body'=>$body];
}
