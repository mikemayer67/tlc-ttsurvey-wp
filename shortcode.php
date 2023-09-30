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

function is_first_survey_on_page()
{
  static $is_first = true;

  if($is_first) {
    $is_first = false;
    return true;
  } else {
    log_error("Cannot include multiple tlc-ttsurvey shortcodes on a given page");
    if(current_user_can('edit_pages')) {
      set_status_error("Can only include one tlc-ttsurvey shortcode per page");
      add_status_message();
    }
    return false;
  }
}

const INFO_STATUS = 0;
const WARNING_STATUS = 1;
const ERROR_STATUS = 2;
function status_message($msg=null,$level=INFO_STATUS)
{
  static $status = null;
  if(!is_null($msg))
  {
    if($msg) {
      $status = [$level,$msg];
    } else {
      $status = null;
    }
  }
  return $status;
}
function set_status_info($msg) { status_message($msg,INFO_STATUS); }
function set_status_warning($msg) { status_message($msg,WARNING_STATUS); }
function set_status_error($msg) { status_message($msg,ERROR_STATUS); }


function shortcode_page($page=null)
{
  static $shortcut_page = null;
  if($page) { $shortcut_page = $page; }
  return $shortcut_page;
}



function handle_shortcode($attr,$content=null,$tag=null)
{
  if(!is_first_survey_on_page()) { return; }

  register_shortcode_scripts();

  ob_start();

  echo "<div id=tlc-ttsurvey>";
  add_noscript();
  add_status_message();
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
  <p class=noscript>If you cannot turn on Javascript, you may want to complete a paper copy of the survey. <?=$download?></p>
  </noscript>
<?php
}

function add_status_message()
{
  $status = status_message();
  if(is_null($status)) { return ; }

  $classes = explode(' ','status w3-panel w3-card w3-border w3-leftbar');

  [$level,$msg] = $status;
  switch($level)
  {
  case INFO_STATUS:
    $classes[] = 'w3-pale-green';
    $classes[] = 'w3-border-green';
    break;
  case WARNING_STATUS:
    $classes[] = 'w3-pale-yellow';
    $classes[] = 'w3-border-orange';
    break;
  case ERROR_STATUS:
    $classes[] = 'w3-pale-red';
    $classes[] = 'w3-border-red';
    break;
  default:
    log_error("Unexpected survey status level encountered: $level");
    return;
  }

  $classes = implode(' ',$classes);
  echo("<div class='$classes'>$msg</div>");
}

function add_shortcode_content()
{
  $active_year = active_survey_year();
  if(!$active_year) {
    require plugin_path('shortcode/inactive_survey.php');
    return;
  }

  $page = shortcode_page();
  if($page) {
    require plugin_path("shortcode/$page.php");
    return;
  }

  $page_uri=$_SERVER['REQUEST_URI'];
  log_info("GET: ".print_r($_GET,true));
  log_info("URL: ".print_r(parse_url($page_uri),true));
  if(key_exists('tlcpage',$_GET)) {
    $page = $_GET['tlcpage'];
    if(in_array($page,['register','login','page','senduserid']))
    {
      enqueue_login_ajax_scripts();
      require plugin_path("shortcode/$page.php");
    } elseif( $page=='survey' ) {
      require plugin_path("shortcode/survey.php");
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
    enqueue_login_ajax_scripts();
    require plugin_path('shortcode/login.php');
  }
}

/**
 * Add the script and style enqueing
 */

function register_shortcode_scripts()
{
  wp_register_script(
    'tlc_ttsurvey_shortcode',
    plugin_url('js/shortcode.js'),
    array('jquery'),
    '1.0.3',
    true
  );

  wp_localize_script(
    'tlc_ttsurvey_shortcode',
    'shortcode_vars',
    array(),
  );

  wp_enqueue_script('tlc_ttsurvey_shortcode');
}

function enqueue_login_ajax_scripts()
{
  wp_register_script(
    'tlc_ttsurvey_login_ajax',
    plugin_url('js/login_ajax.js'),
    array('jquery'),
    '1.0.3',
    true
  );

  $key = 'login_ajax';
  wp_localize_script(
    'tlc_ttsurvey_login_ajax',
    'login_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array($key,wp_create_nonce($key)),
    ),
  );

  wp_enqueue_script('tlc_ttsurvey_login_ajax');
}

wp_enqueue_style('tlc-ttsurvey', plugin_url('css/tlc-ttsurvey.css'));
wp_enqueue_style('wp-w3-css',plugin_url('css/tlc-w3.css'));

add_shortcode('tlc-ttsurvey', ns('handle_shortcode'));
