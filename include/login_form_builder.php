<?php
namespace TLC\TTSurvey;

/**
 * Common Look and Feel elements ued by all survey pages, forms, etc.
 **/

require_once plugin_path('include/logger.php');

function start_login_form($header,$name) 
{
  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  echo("<div class='login_form $name w3-container w3-card-4 w3-border w3-border-blue-gray'>");
  echo("<header class='w3-container w3-blue-gray'><h3>$header</h3></header>");
  echo("<form class='login w3-container' method=post action='$form_uri'>");
  wp_nonce_field(LOGIN_FORM_NONCE);
}

function close_login_form()
{
  // must close all DOM elements opened in start_login_form
  echo("</form></div>");
}

function add_login_instructions($instructions)
{
  echo("<div>");
  foreach($instructions as $instruction)
  {
    echo("<div class=instruction>$instruction</div>");
  }
  echo("</div>");
}

function add_login_input($type,$name,$label,$kwargs=array())
{
  $info = $kwargs['info'] ?? null;
  if($info) {
    $info_link = "tlc-ttsurvey-$name-info";
    $info_icon = '<img src='.plugin_url('img/icons8-info.png').' width=18 height=18>';
    $info_trigger = "<a class='info-trigger' data-target='$info_link'>$info_icon</a>";
  }

  echo("<!-- $label -->");
  echo("<div class='input $name'>");

  if(in_array($type,['text','password','email']))
  {
    echo("<div class='label w3-container'><label>$label</label>");
    if($info) { echo($info_trigger); }
    echo("<div class='w3-right error $name'></div>");
    echo("</div>"); // ends label div

    $optional = $kwargs['optional'] ?? False;

    $classes = ['w3-input'];
    if($optional) {
      $required = 'placeholder=[optional]';
    } else {
      $required = 'required';
      $classes[] = 'empty';
    }
    $value = $kwargs['value'] ?? '';
    if($value) { $value = "value='$value'"; }

    $classes = implode(' ',$classes);
    echo("<input class='$classes primary' type=$type name=$name $value $required>");

    if($kwargs['confirm'] ?? False) {
      $placeholder = "placeholder='Conform $label'";
      $required = $optional ? '' : 'required';
      echo("<input class='$classes confirm' type=$type name='$name-confirm' $value $placeholder $required>");
    }
  }  
  elseif($type == "checkbox")
  {
    $checked = ($kwargs['checked'] ?? False) ? 'checked' : '';
    echo("<input class='w3-check' type=checkbox name=$name $checked>");
    echo("<label>$label</label>");
    if($info) { echo($info_trigger); }
  }
  else
  {
    log_error("Unrecognized input type ($type) passed to add_login_name");
  }

  if($info)
  {
    echo("<div id='$info_link' class='info-box w3-container'>");
    echo("<div class='info w3-panel w3-card'><p>$info</p></div>");
    echo("</div>");
  }

  echo("</div>");  // input
}

function add_login_submit($label,$action,$kwargs=array())
{
  $cancel = $kwargs['cancel'] ?? False;

  $btn_classes = 'w3-button w3-section w3-ripple w3-right w3-margin-left';
  $submit_classes = "submit $btn_classes w3-blue-gray";
  $cancel_classes = "cancel $btn_classes w3-light-gray";

  $action = "name=action value='$action'";

  echo("<!-- Button bar-->");
  if($cancel)
  {
    $cancel_attr= "name=action value=cancel formnovalidate";
    echo("<div class='w3-bar'>");
    echo("<button class='$submit_classes' $action>$label</button>");
    echo("<button class='$cancel_classes' $cancel_attr>Cancel</button>");
    echo("</div>");
  }
  else
  {
    echo("<div>");
    echo("<button class='$submit_classes w3-block' $action>$label</button>");
    echo("</div>");
  }
}

function add_login_links($links)
{
  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  echo("<div class='w3-panel'>");
  foreach($links as $link)
  {
    [$label,$page,$side] = $link;
    $page_uri = "$form_uri?tlcpage=$page";
    echo("<div class='w3-$side'><a href='$page_uri'>$label</a></div>");
  }
  echo("</div>");
}
