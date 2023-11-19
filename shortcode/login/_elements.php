<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

require_once plugin_path('include/const.php');
require_once plugin_path('include/logger.php');
require_once plugin_path('include/login.php');

wp_enqueue_style('tlc-ttsurvey-login', plugin_url('shortcode/css/login.css'));

/****************************************************************
 **
 ** Common Look and Feel for all login forms
 **
 ****************************************************************/

function start_login_form($header,$name) 
{
  $form_uri = survey_url();

  echo "<div id='login' class='$name'>";
  echo "<header>$header</header>";
  echo "<form class='login' method='post' action='$form_uri'>";
  wp_nonce_field(LOGIN_FORM_NONCE);
  add_hidden_input('refresh',1);
  add_hidden_input('status','');
}

function add_hidden_input($name,$value)
{
  echo "<input type='hidden' name='$name' value='$value'>";
}

function close_login_form()
{
  // must close all DOM elements opened in tlc-ttsurvey-login
  echo "</form></div>";
}

function add_login_instructions($instructions)
{
  echo "<div>";
  foreach($instructions as $instruction) {
    echo "<div class='instruction'>$instruction</div>";
  }
  echo "</div>";
}

/**
 * Input Fields
 *
 * Recognized input types:
 *   userid 
 *   password
 *   username
 *   email
 *   remember
 *
 * Recognized kwargs
 *   name: defaults to type
 *   label: defaults to ucfirst of name
 *   value: defaults to null
 *   optional: defaults to false
 *   confirm: defaults to false (only applies to password)
 *   info: defaults to null
 **/
function add_login_input($type,$kwargs=array())
{
  $name = $kwargs['name'] ?? $type;
  $label = $kwargs['label'] ?? ucwords($name);
  $value = stripslashes($kwargs['value'] ?? null);
  $optional = $kwargs['optional'] ?? False;
  $confirm = $kwargs['confirm'] ?? False;

  $info = $kwargs['info'] ?? null;
  if($info) {
    $info_link = "tlc-ttsurvey-$name-info";
    $info_icon = '<img src='.plugin_url('img/icons8-info.png').' width=18 height=18>';
    $info_trigger = "<a class='info-trigger' data-target='$info_link'>$info_icon</a>";
  }

  echo "<!-- $label -->";
  echo "<div class='input $name'>";

  # add label unless type is 'remember
  if( $type == 'remember' )
  {
    $checked = $value ? 'checked' : '';
    echo "<input type='checkbox' name='$name' $checked>";
    echo "<label>$label</label>";
    if($info) { echo($info_trigger); }
  }
  else
  {
    echo "<div class='label'><label>$label</label>";
    if($info) { echo($info_trigger); }
    echo "<div class='error $name'></div>";
    echo "</div>"; // ends label div

    if($optional) {
      $classes = 'text-entry';
      $extra = "placeholder='[optional]'";
    } else {
      $classes = "text-entry empty";
      $extra = 'required';
    }

    switch($type) {
    case 'username':
    case 'userid':
      $type = "text";
    case 'email';
      if($value) { $extra = "value=\"$value\" $extra"; }
      $input_attrs = array("class='$classes' name='$name' $extra");
      break;

    case 'password':
      if($value) { $extra = "value=\"$value\" $extra"; }
      if($confirm) {
        # confirm overrides the optional parameter ... always required
        $input_attrs = array(
          "class='text-entry entry empty primary' name='$name' required",
          "class='text-entry entry empty confirm' name='$name-confirm' required",
        );
      } else {
        $input_attrs = array("class='$classes' name='$name' $extra");
      }
      break;

    default:
      log_error("Unrecognized input type ($type) passed to add_login_name");
      break;
    }

    foreach( $input_attrs as $attr ) {
      echo "<input type='$type' $attr>";
    }
  }
  if($info)
  {
    echo "<div id='$info_link' class='info-box'>";
    echo "<div class='info'><p>$info</p></div>";
    echo "</div>";
  }
  echo "</div>";  // input


}

function add_resume_buttons()
{
  $tokens = cookie_tokens();
  if(!$tokens) { return; }

  $icon = plugin_url('img/icons8-delete_sign.png');
  $class = 'submit resume token';

  echo "<div class='resume-label'>Resume Survey as:</div>";
  echo "<div class='resume-box'>";
  foreach($tokens as $userid=>$token) {
    $user = User::from_userid($userid);
    if($user) {
      $username = $user->username();
      $value = "resume:$userid:$token";
      echo "<div class='button-box'>";
      echo "<button class='$class' name='resume' value='$userid:$token' formnovalidate>";
      echo "<div class='username'>$username</div>";
      echo "<div class='userid'>$userid</div>";
      echo "</button>";
      $forget_url = survey_url() . "&forget=$userid";
      echo "<div class='forget'>";
      echo "<a href='$forget_url' data-userid='$userid'><img src='$icon'></a>";
      echo "</div>"; // forget
      echo "</div>"; // button-box
    }
  }
  echo "</div>";
  echo "<div class='resume-label'>Or Login as:</div>";
}

function add_login_submit($label,$action,$cancel=False)
{
  echo "<!-- Button bar-->";
  if($cancel)
  {
    echo "<div class='submit-bar'>";
    echo "<button class='submit right' name='action' value='$action'>$label</button>";
    echo "<button class='cancel right' name='action' value='cancel' formnovalidate>Cancel</button>";
    echo "</div>";
  }
  else
  {
    echo "<div class='submit-bar'>";
    echo "<button class='submit full' name='action' value='$action'>$label</button>";
    echo "</div>";
  }
}

function add_login_links($links)
{
  $form_uri = survey_url();

  echo "<div class='links-bar'>";
  foreach($links as $link)
  {
    [$label,$page,$side] = $link;
    $page_uri = "$form_uri&tlcpage=$page";
    echo "<div class='$side $page'><a href='$page_uri'>$label</a></div>";
  }
  echo "</div>";
}

