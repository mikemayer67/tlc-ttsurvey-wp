<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('logger.php');
require_once plugin_path('settings.php');
require_once plugin_path('participant.php');
require_once plugin_path('login.php');

/**
 * handle login 

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
  wp_enqueue_style('tlc-ttsurvey', plugin_url('css/tlc-ttsurvey.css'));
  wp_enqueue_script('shortcode_scripts');

  $login_cookie = LoginCookie::instance();
  $userid = $login_cookie->active_userid();
  $anonid = $login_cookie->active_anonid();

  $html = "";
  $html .= "<div class=tlc-ttsurvey-container>";
  if( $userid == null ) {
    $html .= "No current user (add login screen here...)";
  } elseif( $anonid == null ) {
    $html .= "Current user has id $userid, but no anonymous id";
  } else {
    $html .= "Current user has id $userid and anonymous id $anonid";
  }


  $html .= "</div>";

  return $html;
}

wp_register_script(
  'shortcode_scripts',
  plugin_url('js/shortcode.js'),
  array('jquery'),
  '1.0.3',
  true
);

wp_localize_script(
  'shortcode_scripts',
  'shortcode_vars',
  array('year'=>Settings::instance()->get(ACTIVE_YEAR_KEY))
);
