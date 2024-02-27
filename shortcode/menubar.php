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
  echo "<div class='menubox main'>";
  echo "  <div class='menubar-item survey-name'>$survey_name</div>";
  echo "  <div class='menubar-item status'>$status</div>";
  echo "  <div class='menubar-item user'>";
  echo "    <button class='menu-btn user'><span class='name'>$fullname</span><img src='$icon_url'></button>";
  echo "    <div class='menu user'>";
  echo "      <a class='edit-user-name'>Update Name</a>";
  if($email) {
    echo "    <a class='edit-user-email'>Update Email</a>";
    echo "    <a class='drop-user-email'>Remove Email</a>";
  } else {
    echo "    <a class='add-user-email'>Add Email</a>";
  }
  echo "      <a class='change-password'>Change Password</a>";
  echo "      <hr class='menu-sep'>";
  echo "      <a href='$form_uri?logout=1'>Log Out</a>";
  echo "    </div>"; // menu.user
  echo "  </div>"; // menubar-item.user
  echo "</div>"; // menubox.main
  echo "<div class='menubox mobile'>";
  echo "</div>"; //menubox.mobile
  echo "</nav>";
}

function add_user_profile_editor($userid)
{
  echo "<div class='modal user-profile'>";
  echo "<div class='dialog user-profile'>";
  echo "<form class='user-profile'>";
  wp_nonce_field(USER_PROFILE_NONCE);
  $user = User::from_userid($userid);
  add_user_name_editor($user);
  add_user_email_editor($user);
  add_user_password_editor($user);
  echo "<div class='button-box'>";
  echo "<button class='cancel'>Cancel</button>";
  echo "<button class='submit'>Update</button>";
  echo "</div>"; // button-box
  echo "</form>";
  echo "</div>"; // dialog
  echo "</div>"; // modal
}

function start_editor_content($user_property)
{
  echo "<div class='editor-body $user_property'>";
}

function end_editor_content()
{
  echo "</div>"; // editor-body
}

function add_label_box($label,$key)
{
  $info_link = "tlc-ttsurvey-$key-info";
  $icon_url = plugin_url('img/icons8-info.png');
  $info_icon = "<img src='$icon_url'>";

  echo "<div class='label-box'>";
  echo "<label for='tlcsurvey-entry-box-$key'>$label:</label>";
  echo "<a class='info-trigger' data-target='$info_link'>$info_icon</a>";
  echo "<div class='error $key'>error</div>";
  echo "</div>";
}

function add_info_box($key,$info)
{
  $info_link = "tlc-ttsurvey-$key-info";
  echo "<div id='$info_link' class='info-box'>";
  echo "<div class='info'><p>$info</p></div>";
  echo "</div>";
}

function add_user_name_editor($user)
{
  $fullname = $user->fullname();
  start_editor_content('name');
  add_label_box('Name','name');
  echo "<div class='entry-box name'>";
  echo "<input id='tlcsurvey-entry-box-name' type='text' class='text-entry name' name='name' value='$fullname' data-default='$fullname' placeholder='Full Name' autocomplete='name'>";
  echo "</div>";
  add_info_box('name',info_text('fullname'));
  end_editor_content();
}

function add_user_email_editor($user)
{
  $email = $user->email();
  $empty = $email ? "" : "empty";
  start_editor_content('email');
  add_label_box('Email','email');
  echo "<div class='entry-box email'>";
  echo "<input id='tlcsurvey-entry-box-email' type='email' class='text-entry email $empty' name='email' value='$email' data-default='$email' placeholder='Email Address' autocomplete='email'>";
  echo "</div>";
  add_info_box('email',info_text('email'));
  end_editor_content();
}

function add_user_password_editor($userid)
{
  start_editor_content('password');
  add_label_box('Password','password');
  echo "<div class='entry-box'>";
  echo "<input id='tlcsurvey-entry-box-password' type='password' class='text-entry primary empty' name='password' placeholder='New Password' autocomplete='new-password'>";
  echo "</div><div class='entry-box'>";
  echo "<input type='password' class='text-entry confirm empty' name='password-confirm' placeholder='Confirm Password' autocomplete='new-password'>";
  echo "</div>";
  add_info_box('password',info_text('password'));
  end_editor_content();
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

