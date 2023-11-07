<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

require_once plugin_path('include/logger.php');
require_once plugin_path('include/login.php');

/****************************************************************
 **
 ** Login form container
 ** 
 ** This is what will repopulated when AJAX is used to swap
 **  out the login form.
 **
 ****************************************************************/

function add_login_content($page=null)
{
  if(!$page) {
    $tokens = cookie_tokens();
    if($tokens) { $page = 'resume'; }
    else        { $page = 'userid';  }
  }
  $page = plugin_path("shortcode/login/$page.php");
  if(!file_exists($page)) {
    require plugin_path("shortcode/bad_page.php");
    return false;
  }

  require $page;
  return true;
}


function enqueue_login_script()
{
  log_dev("enqueue_login_script");

  wp_register_script(
    'tlc_ttsurvey_login',
    plugin_url('shortcode/js/login.js'),
    array('jquery'),
    '1.0.3',
    true
  );

  wp_localize_script(
    'tlc_ttsurvey_login',
    'login_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('login',wp_create_nonce('login')),
      'survey_url' => survey_url(),
    ),
  );

  wp_enqueue_script('tlc_ttsurvey_login');
}


/****************************************************************
 **
 ** Userid/Password
 **   with links to 
 **
 ****************************************************************/

