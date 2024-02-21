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
  $email = $user->email();

  $status = "Status";

  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  $icon_url = plugin_url('/img/icons8-down.png');

  echo "<nav class='menubar'>";
  echo "<div class='menubar-item survey-name'>$survey_name</div>";
  echo "<div class='menubar-item status'>$status</div>";
  echo "<div class='menubar-item user'>";
  echo "<button class='menu-btn user'>$fullname<img src='$icon_url'></button>";
  echo "<div class='menu user'>";
  echo "<a class='edit-user-name'>Update Name</a>";
  if($email) {
    echo "<a class='edit-user-email'>Update Email</a>";
    echo "<a class='drop-user-email'>Remove Email</a>";
  } else {
    echo "<a class='add-user-email'>Add Email</a>";
  }
  echo "<a class='change-password'>Change Password</a>";
  echo "<hr class='menu-sep'>";
  echo "<a href='$form_uri?logout=1'>Log Out</a>";
  echo "</div></div></nav>";
}

function add_user_profile_editor($userid)
{
  echo "<div class='modal user-profile'>";
  echo "<div class='dialog user-profile'>";
  $user = User::from_userid($userid);
  add_user_name_editor($user);
  add_user_email_editor($user);
  add_user_password_editor($user);
  echo "</div></div>";
}

function start_editor_content($user_property)
{
  echo "<form class='user-profile $user_property'>";
  wp_nonce_field(USER_PROFILE_NONCE);
  echo "<div class='editor-body $user_property'>";
  echo "<div class='entry-box'>";
}

function end_editor_content()
{
  echo "</div>"; // entry-box
  echo "<div class='button-box'>";
  echo "<button class='cancel'>Cancel</button>";
  echo "<button class='submit'>Update</button>";
  echo "</div>"; // button-box
  echo "</div>"; // editor-body
  echo "</form>";
}

function add_info_trigger($key)
{
  $info_link = "tlc-ttsurvey-$key-info";
  $icon_url = plugin_url('img/icons8-info.png');
  $info_icon = "<img src='$icon_url' width=18 height=18>";
  echo "<a class='info-trigger' data-target='$info_link'>$info_icon</a>";
}

function add_user_name_editor($user)
{
  $fullname = $user->fullname();
  $trigger_key = "name-editor";
  start_editor_content('name');
  echo "<div class='entry-row'>";
  echo "<label>Name:</label>";
  echo "<input type='text' class='text-entry name' name='name' value='$fullname'>";
  add_info_trigger($trigger_key);
  echo "</div>"; // entry-row
  end_editor_content();
}

function add_user_email_editor($userid)
{
  start_editor_content('email');
  echo "<label>Email:</label>";
  end_editor_content();
}

function add_user_password_editor($userid)
{
  start_editor_content('password');
  end_editor_content();
}

function add_user_profile_editor_delete_me($userid)
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

