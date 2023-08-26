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
  $login_cookie = LoginCookie::instance();
  $userid = $login_cookie->active_userid();
  $anonid = $login_cookie->active_anonid();


  ob_start();
?>
<div class=tlc-ttsurvey-container>

<noscript>
<div class=tlc-ttsurvey-noscript>This survey works best with Javascript enabled</div>
<p class=tlc-ttsurvey-noscript>If you cannot (or prefer not) to turn on Javascript, you may need to complete a paper copy of the survey.
<?php
  $pdf_uri = Settings::pdf_uri();
  if(!empty($pdf_uri)) {
    echo " You can download a PDF version <a target='_blank' href='$pdf_uri'>here</a>.</p>";
  }
?>
</noscript>

<?php
  if( $userid == null ) {
    require plugin_path('shortcode/login_form.php');
  } elseif( $anonid == null ) {
    echo "Current user has id $userid, but no anonymous id";
  } else {
    echo "Current user has id $userid and anonymous id $anonid";
  }
?>
</div>
<?php
  $html = ob_get_contents();
  ob_flush_contents();
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

wp_enqueue_style('tlc-ttsurvey', plugin_url('css/tlc-ttsurvey.css'));
wp_enqueue_script('shortcode_scripts');

