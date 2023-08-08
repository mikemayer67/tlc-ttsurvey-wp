<?php
namespace TLC\TTSurvey;

/**
 * TLC TTSurvey  plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once 'logger.php';

/**
 * handle the plugin shortcode
 *
 * shortcode format: [tag key=value ... ]content[/tag]
 *
 * @param dict $attr shortcode attributes
 * @param string $content shortcode content
 * @param string $tag shortcode tag
 */

function handle_shortcode($attr,$content=null,$tag=null)
{
  static $first = true;
  if(!$first) {
    return;
  } else {
    $first = false;
  }

  wp_enqueue_style('tlc-ttsurvey-shortcode', tlc_plugin_url('css/tlc-ttsurvey-shortcode.css'));

  $html = "<h3>Nothing to See</h3>";

  return $html;
}

add_shortcode('tlc-ttsurvey', ns('handle_shortcode'));
