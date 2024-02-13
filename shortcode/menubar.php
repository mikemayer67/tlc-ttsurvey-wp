<?php
namespace TLC\TTSurvey;

/**
 * Handle the actual survey
 */

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/users.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/const.php');
require_once plugin_path('shortcode/login/_elements.php');

function add_survey_menubar($userid)
{
  $survey = current_survey();
  $survey_name = $survey->name() . " Time & Talent Survey";

  $user = User::from_userid($userid);
  $fullname = $user->fullname();

  $status = "Status";

  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  $icon_url = plugin_url('/img/icons8-down.png');

  echo <<<MENUBAR_HTML
    <nav class='menubar'>
      <div class='menubar-item survey-name'>$survey_name</div>
      <div class='menubar-item status'>$status</div>
      <div class='menubar-item user'>
      <button class='menu-btn user'>$fullname<img src='$icon_url'></button>
        <div class='menu user'>
            <a href='' class='user-profile'>Edit Profile</a>
            <a href='$form_uri?logout=1'>Log Out</a>
        </div>
      </div>
    </nav>
    MENUBAR_HTML;
}

function add_user_profile_editor($userid)
{
  log_dev("add_user_profile_editor($userid)");
  $user = User::from_userid($userid);
  log_dev("ok");
  $fullname = $user->fullname();
  log_dev($fullname);
  $email = $user->email();
  log_dev($email);

  echo "<div class='modal user-profile'>";
  echo "<div class='dialog user-profile'>";
  echo "<form class='user-profile'>";

  wp_nonce_field(USER_PROFILE_NONCE);

  add_login_input("fullname",array(
    "label" => 'Name',
    "value" => $fullname,
    "info" => <<<INFO
      How your name will appear on the survey summary report
      <p class=info-list><b>must</b> contain a valid full name</p>
      <p class=info-list><b>may</b> contain apostrophes</p>
      <p class=info-list><b>may></b> contain hyphens</p>
      <p class=info-list>Extra whitespace will be removed</p>
      INFO
  ));

  add_login_input("new-password",array(
    "name" => "password",
    "optional" => True,
    "info" => <<<INFO
      Used to log into the survey
      <p class=info-list><b>must</b> be between 8 and 128 characters</p>
      <p class=info-list><b>must</b> contain at least one letter</p>
      <p class=info-list><b>may</b> contain: !@%^*-_=~,.</p>
      <p class=info-list><b>may</b> contain spaces</p>
      INFO
  ));

  add_login_input("email",array(
    "optional" => True, 
    "value" => $email,
    "info" => <<<INFO
      The email address is <b>optional</b>. It will only be used in conjunction with 
      this survey. It will be used to send you:
      <p class=info-list>confirmation of your registration</p>
      <p class=info-list>notifcations on your survey state</p>
      <p class=info-list>login help (on request)</p>
      INFO
  ));

  add_login_submit("Update",'update',true);


  echo "</form></div></div>";
}

function enqueue_menubar_script()
{
  wp_register_script(
    'tlc_menubar_script',
    plugin_url('shortcode/js/menubar.js'),
    array('jquery'),
    '1.0.3',
    true
  );

  wp_localize_script(
    'tlc_menubar_script',
    'menubar_vars',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce' => array('menubar',wp_create_nonce('menubar')),
    ),
  );

  wp_enqueue_script('tlc_menubar_script');
}

