<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }
if( !plugin_admin_can('content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/logger.php');

function gen_survey_focuses($survey)
{
  $content = $survey->content();
  $content = $content['survey'] ?? [];
  $focuses = [];
  foreach( $content as $focus ) {
    $focuses[] = gen_survey_focus($focus);
  }
  return $focuses;
}

function gen_survey_focus($focus)
{
  $name = $focus['name'];
  $description = $focus['description'] ?? "$name stuff content goes here";
  $tgt = str_replace(' ','-',strtolower($name));

  $tab = "<li class='nav-tab focus $tgt' data-target='$tgt'>$name</li>";

  $body = <<<BODY_HTML
    <div class='focus $tgt'>
      <div class='metadata'>
        <table><tr>
          <td>Name:</td>
          <td><input class='focus-metadata-name' value='$name'></td>
        </tr><tr>
          <td>Description:</td>
          <td><input class='focus-metadata-description' value='$description'></td>
        </tr></table>
      </div>
      <div class='content'>
        <div class='info'>$description</div>
      </div>
    </div>
  BODY_HTML;

  return ['tab'=>$tab, 'body'=>$body];
}
