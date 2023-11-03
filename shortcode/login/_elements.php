<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

require_once plugin_path('include/logger.php');

wp_enqueue_style('tlc-ttsurvey-login', plugin_url('shortcode/css/login.css'));

/****************************************************************
 **
 ** Common Look and Feel for all login forms
 **
 ****************************************************************/

function start_login_form($header,$name) 
{
  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  $w3_card = 'w3-container w3-card-4 w3-border w3-border-blue-gray';
  echo "<div id='tlc-ttsurvey-login' class='card $name $w3_card'>";
  echo "<header class='w3-container w3-blue-gray'><h3>$header</h3></header>";
  echo "<form class='login w3-container' method='post' action='$form_uri'>";
  wp_nonce_field(LOGIN_FORM_NONCE);
  echo "<input type='hidden' name='refresh' value='1'>";
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
  $value = $kwargs['value'] ?? null;
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
    echo "<input type='checkbox' class='w3-check' name='$name' $checked>";
    echo "<label>$label</label>";
    if($info) { echo($info_trigger); }
  }
  else
  {
    echo "<div class='label w3-container'><label>$label</label>";
    if($info) { echo($info_trigger); }
    echo "<div class='w3-right error $name'></div>";
    echo "</div>"; // ends label div

    if($optional) {
      $classes = 'w3-input';
      $extra = "placeholder='[optional]'";
    } else {
      $classes = "w3-input empty";
      $extra = 'required';
    }

    switch($type) {
    case 'userid':
      $type = "text";
      if($value) { $extra = "value='$value' $extra"; }
      $input_attrs = array("class='$classes' name='$name' $extra");
      break;

    case 'email':
      if($value) { $extra = "value='$value' $extra"; }
      $input_attrs = array("class='$classes' name='$name' $extra");
      break;

    case 'password':
      if($value) { $extra = "value='$value' $extra"; }
      if($confirm) {
        # confirm overrides the optional parameter ... always required
        $input_attrs = array(
          "class='w3-input empty primary' name='$name' required",
          "class='w3-input empty confirm' name='$name-confirm' required",
        );
      } else {
        $input_attrs = array("class='$classes' name='$name' $extra");
      }
      break;

    case 'username':
      $type = "text";
      $extra = $optional ? '' : 'required';
      if($value) { 
        [$first,$last] = $value;
        $extra1 = "value='$first' $extra";
        $extra2 = "value='$last' $extra";
      }
      $input_attrs = array(
       "class='$classes first' name='$name-first' placeholder='First' $extra1",
       "class='$classes last' name='$name-last' placeholder='Last' $extra2",
      );
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
    echo "<div id='$info_link' class='info-box w3-container'>";
    echo "<div class='info w3-panel w3-card'><p>$info</p></div>";
    echo "</div>";
  }

  echo "</div>";  // input
}

function add_login_submit($label,$action,$kwargs=array())
{
  $cancel = $kwargs['cancel'] ?? False;

  $btn_classes = 'w3-button w3-section w3-ripple w3-right w3-margin-left';
  $submit_classes = "submit $btn_classes w3-blue-gray";
  $cancel_classes = "cancel $btn_classes w3-light-gray";

  $action = "name='action' value='$action'";

  echo "<!-- Button bar-->";
  if($cancel)
  {
    $cancel_attr= "name='action' value='cancel' formnovalidate";
    echo "<div class='w3-bar'>";
    echo "<button class='$submit_classes' $action>$label</button>";
    echo "<button class='$cancel_classes' $cancel_attr>Cancel</button>";
    echo "</div>";
  }
  else
  {
    echo "<div>";
    echo "<button class='$submit_classes w3-block' $action>$label</button>";
    echo "</div>";
  }
}

function add_login_links($links)
{
  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  echo "<div class='w3-panel'>";
  foreach($links as $link)
  {
    [$label,$page,$side] = $link;
    $page_uri = "$form_uri?tlcpage=$page";
    echo "<div class='w3-$side $page'><a href='$page_uri'>$label</a></div>";
  }
  echo "</div>";
}

