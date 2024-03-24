<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }
if( !plugin_admin_can('content') ) { 
  echo "<h2>oops... you shouldn't be here</h2>";
  return;
}

require_once plugin_path('include/logger.php');

function gen_survey_focuses($survey,$editable=false)
{
  $content = $survey->content();
  $content = $content['survey'] ?? [];
  $focuses = [];
  foreach( $content as $focus ) {
    $focuses[] = gen_survey_focus($focus,$editable);
  }
  return $focuses;
}

function gen_survey_focus($focus,$editable=false)
{
  $name = $focus['name'];
  $description = $focus['description'] ?? "$name stuff content goes here";
  $tgt = str_replace(' ','-',strtolower($name));

  $tab = "<li class='nav-tab focus $tgt' data-target='$tgt'>$name</li>";

  $metadata = "";
  if($editable) {
    $metadata = <<<METADATA_HTML
      <div class='metadata'>
        <table><tr>
          <td class='label'>Name:</td>
          <td class='value'><input class='focus-metadata-name' value='$name'></td>
          <td class='error'>Error text</td>
        </tr><tr>
          <td class='label'>Description:</td>
          <td class='value'><input class='focus-metadata-description' value='$description'></td>
        </tr><tr>
          <td class='label'>Order:</td>
          <td class='value'>
            <button class='move first'>&lt;&lt;</button>
            <button class='move left'>&lt;</button>
            <button class='move right'>&gt;</button>
            <button class='move last'>&gt;&gt;</button>
          </td>
        </tr></table>
        <button class='close'>Close</button>
      </div>
    METADATA_HTML;
  }

  $content = "content";

  $body = <<<BODY_HTML
    <div class='focus $tgt'>
      $metadata
      <div class='content'>
        <div class='info'>$description</div>
        $content
      </div>
    </div>
  BODY_HTML;


  return ['tab'=>$tab, 'body'=>$body];
}
