<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

const LOGIN_FORM_NONCE = 'tlc-ttsurver-login';

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');
require_once plugin_path('include/login.php');

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
  log_info("enqueue script when shortcode is rendered");
  wp_enqueue_script('shortcode_scripts');

  ob_start();

  echo "<div class='tlc-ttsurvey w3-css'>";
  add_noscript();
  add_shortcode_content();
  echo "</div>";

  $html = ob_get_contents();
  ob_end_clean();
  return $html;
}

function add_noscript()
{
  $pdf_uri = survey_pdf_uri();
  if($pdf_uri) {
    $download = "You can download a PDF version <a target='_blank' href='$pdf_uri'>here</a>.</p>";
  } else {
    $download = "";
  }
?>
  <noscript>
  <div class=noscript>This survey works best with Javascript enabled</div>
  <p class=noscript>If you cannot (or prefer not) to turn on Javascript, you may need to complete a paper copy of the survey. <?=$download?></p>
  </noscript>
<?php
}

function add_shortcode_content()
{
  $active_year = active_survey_year();
  log_dev("active_year: $active_year");
  if(!$active_year) {
    require plugin_path('shortcode/inactive_survey.php');
    return;
  }

  $userid = active_userid();
  $tokens = cookie_tokens();

  if( $userid ) {
    require plugin_path('shortcode/survey.php');
  }
  elseif($tokens) {
    require plugin_path('shortcode/resume_form.php');
  }
  else {
    require plugin_path('shortcode/login_form.php');
  }
}

/**
 * Add the script and style enqueing
 */

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
  array(),
);

wp_enqueue_style('tlc-ttsurvey', plugin_url('css/tlc-ttsurvey.css'));
wp_enqueue_style('wp-w3-css',plugin_url('css/wp-w3.css'));
#wp_enqueue_script('shortcode_scripts');

add_shortcode('tlc-ttsurvey', ns('handle_shortcode'));
