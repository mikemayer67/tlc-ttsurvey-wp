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
 *   new-password
 *   fullname
 *   email
 *   remember
 *
 * Recognized kwargs
 *   name: defaults to type
 *   label: defaults to ucfirst of name
 *   value: defaults to null
 *   optional: defaults to false
 *   info: defaults to null
 **/
function add_login_input($type,$kwargs=array())
{
  $name = $kwargs['name'] ?? $type;
  $label = $kwargs['label'] ?? ucwords($name);
  $value = stripslashes($kwargs['value'] ?? null);
  $optional = $kwargs['optional'] ?? False;
  $info = $kwargs['info'] ?? null;

  echo "<!-- $label -->";
  echo "<div class='input $name'>";

  # add label box

  echo "<div class='label-box'>";
  echo "<label>$label</label>";
  if($info) { 
    $info_link = "tlc-ttsurvey-$name-info";
    $icon_url = plugin_url('img/icons8-info.png');
    $info_icon = "<img src='$icon_url' width=18 height=18>";
    $info_trigger = "<a class='info-trigger' data-target='$info_link'>$info_icon</a>";
    echo($info_trigger); 
  }
  echo "<div class='error $name'></div>";
  echo "</div>"; // label-box

  # add input fields
  
  $value = $value ? "value=\"$value\"" : "";

  $empty = $optional ? '' : 'empty';
  $required = $optional ? "placeholder='[optional]'" : 'required';
  
  if($type=='new-password') 
  {
    echo "<input type='password' class='text-entry entry empty primary' name='$name' required autocomplete='new-password'>";
    echo "<input type='password' class='text-entry entry empty confirm' name='$name-confirm' required autocomplete='new-password'>";
  }
  else
  {
    switch($type) {
    case 'password': $value = '';    break;
    case 'email':                    break;
    default:         $type = 'text'; break;
    }
    echo "<input type='$type' class='text-entry $empty' name='$name' $value $required>";
  }

  # add info box

  if($info)
  {
    echo "<div id='$info_link' class='info-box'>";
    echo "<div class='info'><p>$info</p></div>";
    echo "</div>";
  }

  # close the input box
  echo "</div>";  // input
}


function add_login_checkbox($name, $kwargs=array())
{
  $label = $kwargs['label'] ?? ucwords($name);
  $checked = stripslashes($kwargs['value'] ?? False) ? 'checked' : '';
  $info = $kwargs['info'] ?? null;

  echo "<!-- $label -->";
  echo "<div class='input $name'>";
  
  echo "<div class='label-box'>";
  echo "<input type='checkbox' name='$name' $checked>";
  echo "<label>$label</label>";

  if($info)
  {
    $info_link = "tlc-ttsurvey-$name-info";
    $icon_url = plugin_url('img/icons8-info.png');
    $info_icon = "<img src='$icon_url' width=18 height=18>";
    $info_trigger = "<a class='info-trigger' data-target='$info_link'>$info_icon</a>";
    // close out the label-box with the info trigger
    echo($info_trigger);
    echo "</div>";

    // start the info-box
    echo "<div>";
    echo "<div id='$info_link' class='info-box'>";
    echo "<div class='info'><p>$info</p></div>";
    echo "</div>";
  }
  echo "</div>"; // label-box (if no-info) or info-box (if info present)
  echo "</div>"; // input box
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
      $fullname = $user->fullname();
      $value = "resume:$userid:$token";
      echo "<div class='button-box'>";
      echo "<button class='$class' name='resume' value='$userid:$token' formnovalidate>";
      echo "<div class='fullname'>$fullname</div>";
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
    echo "<button class='submit' name='action' value='$action'>$label</button>";
    echo "<button class='cancel' name='action' value='cancel' formnovalidate>Cancel</button>";
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

