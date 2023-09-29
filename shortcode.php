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

$page_has_shortcode = False;

function handle_shortcode($attr,$content=null,$tag=null)
{
  global $page_has_shortcode;
  if($page_has_shortcode) {
    log_error("Cannot include multiple tlc-ttsurvey shortcodes on a given page");
    if(current_user_can('edit_pages')) {
      set_survey_error("Can only include one tlc-ttsurvey shortcode per page");
      add_status();
    }
    return;
  }
  $page_has_shortcode = True;

  log_info("enqueue script when shortcode is rendered");
  wp_enqueue_script('tlc_shortcode_scripts');

  ob_start();

  echo "<div id=tlc-ttsurvey>";
  add_noscript();
  add_status();
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

function add_status()
{
  global $survey_status;
  if(is_null($survey_status)) { return ; }
  switch($survey_status[0]) 
  {
    case SURVEY_STATUS_INFO:
      $w3_status = "w3-pale-green w3-border-green";
      break;
    case SURVEY_STATUS_WARNING;
      $w3_status = "w3-pale-yellow w3-border-orange";
      break;
    case SURVEY_STATUS_ERROR:
      $w3_status = "w3-pale-red w3-border-red";
      break;
    default:
      log_error("Unexpected survey status level encountered: $survey_status[0]");
      return;
  }

  echo("<div class='status w3-panel w3-card $w3_status w3-border w3-leftbar'>$survey_status[1]</div>");
}

function add_shortcode_content()
{
  $active_year = active_survey_year();
  if(!$active_year) {
    require plugin_path('shortcode/inactive_survey.php');
    return;
  }

  global $shortcode_page;
  if($shortcode_page) {
    require plugin_path("shortcode/$shortcode_page.php");
    return;
  }
  $page_uri=$_SERVER['REQUEST_URI'];
  log_info("GET: ".print_r($_GET,true));
  log_info("URL: ".print_r(parse_url($page_uri),true));
  if(key_exists('tlcpage',$_GET)) {
    $page = $_GET['tlcpage'];
    if(in_array($page,['register','login','page','senduserid','survey']))
    {
      require plugin_path("shortcode/$page.php");
    } else {
      require plugin_path('shortcode/bad_page.php');
    }
    return;
  }

  $userid = active_userid();
  $tokens = cookie_tokens();

  if( $userid ) {
    require plugin_path('shortcode/survey.php');
  }
  elseif($tokens) {
    require plugin_path('shortcode/resume.php');
  }
  else {
    require plugin_path('shortcode/login.php');
  }
}

/**
 * Add the script and style enqueing
 */

wp_register_script(
  'tlc_shortcode_scripts',
  plugin_url('js/shortcode.js'),
  array('jquery'),
  '1.0.3',
  true
);

wp_localize_script(
  'tlc_shortcode_scripts',
  'shortcode_vars',
  array(),
);

wp_enqueue_style('tlc-ttsurvey', plugin_url('css/tlc-ttsurvey.css'));
wp_enqueue_style('wp-w3-css',plugin_url('css/tlc-w3.css'));

add_shortcode('tlc-ttsurvey', ns('handle_shortcode'));
