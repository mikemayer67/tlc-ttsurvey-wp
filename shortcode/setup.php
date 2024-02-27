<?php
namespace TLC\TTSurvey;

/**
 * TLC Time and Talent plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/login.php');

/**
 * handle the plugin shortcode
 *
 * shortcode format: [tag key=value ... ]content[/tag]
 *
 * @param dict $attr shortcode attributes
 * @param string $content shortcode content
 * @param string $tag shortcode tag
 */

function survey_url()
{
  return parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH).'?tlc=1';
}

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

const INFO_STATUS = 'info';
const WARNING_STATUS = 'warning';
const ERROR_STATUS = 'error';
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
function clear_status() { status_message(null); }


function _shortcode_page($action,$page=null)
{
  static $_shortcode_page = null;
  if($action == '_get')       { return $_shortcode_page;  } 
  elseif($action == '_set')   { $_shortcode_page = $page; } 
  elseif($action == '_clear') { $_shortcode_page = null;  }
}

function current_shortcode_page()
{
  // returns first hit of:
  // - page explicitly set with set_current_shortcode_page
  // - page extracted from the URL query (tlcpage)
  // - null
  return _shortcode_page('_get') ?? $_GET['tlcpage'] ?? null;
}
function set_current_shortcode_page($page) { _shortcode_page('_set',$page); }
function clear_current_shortcode_page()    { _shortcode_page('_clear');     }


function handle_shortcode($attr,$content=null,$tag=null)
{
  if(!is_first_survey_on_page()) { return; }

  ob_start();

  echo "<div id='tlc-ttsurvey'>";
  echo "<noscript>";
  echo "<div class='noscript'>";
  echo "<p class='header'>Javascript Disabled</p>";
  echo "<div>";
  echo "<p>The Time & Talent Survey requries that Javascript be enabled on your browser.</p>";
  echo "<p>For help enabling Javascript on your browser, see ";
  echo "<a href='https://www.computerhope.com/issues/ch000891.htm' target='_blank'>this link.</a>";
  echo "</p>";
  $pdf_uri = survey_pdf_uri();
  if($pdf_uri) {
    echo "<p>If Javascript is not an option, you may download a ";
    echo "<a target='_blank' href='$pdf_uri'>PDF version</a> of the survey.</p>";
  }
  echo "</div></div>";
  echo "</noscript>";
  echo "<div class='javascript-required'>";
  add_shortcode_content();
  echo "</div></div>"; // tlc-ttsurvey

  $html = ob_get_contents();
  ob_end_clean();
  return $html;
}

function add_status_message()
{
  $status = status_message();
  if($status) {
    [$level,$msg] = $status;
  } else {
    $level = 'none';
    $msg = "";
  }
  echo "<div id='status-message' class='status $level'>$msg</div>";
}

/**
 * This function should only be called when INITIALLY loading the shortcode content
 *   It is not called when the content is replaced by an AJAX response handler
 **/
function add_shortcode_content()
{
  $active_survey = active_survey();
  if(!$active_survey) {
    require plugin_path('shortcode/inactive_survey.php');
    return;
  }

  $page = current_shortcode_page();
  $userid = active_userid();

  $content_loaded = false;
  if($userid && !$page) {
    require_once plugin_path("shortcode/survey.php");
    if(add_survey_content($userid)) {
      return;
    }
  }

  require_once plugin_path("shortcode/login.php");
  if(add_login_content($page)) {
    enqueue_login_script();
    return;
  }

  require plugin_path("shortcode/bad_page.php");
}

function enqueue_shortcode_style()
{
  wp_enqueue_style('tlc-ttsurvey', plugin_url('shortcode/css/shortcode.css'));
  wp_enqueue_style('wp-w3-css',plugin_url('shortcode/css/core.css'));

  wp_register_script(
    'tlc_ttsurvey_shortcode',
    plugin_url('shortcode/js/shortcode.js'),
    array('jquery'),
    '1.0.3',
    true
  );
  wp_localize_script(
    'tlc_ttsurvey_shortcode',
    'shortcode_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('shortcode',wp_create_nonce('shortcode')),
      'scroll' => $_REQUEST['tlc'] ?? 0,
    ),
  );
  wp_enqueue_script('tlc_ttsurvey_shortcode');
}

add_action('wp_enqueue_scripts',ns('enqueue_shortcode_style'));
add_shortcode('tlc-ttsurvey', ns('handle_shortcode'));
