<?php
namespace TLC\TTSurvey;

/**
 * Common Look and Feel elements ued by all survey pages, forms, etc.
 **/

require_once plugin_path('include/logger.php');

function start_login_form($header,$name) 
{
  $form_uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

  $nonce = wp_nonce_field(LOGIN_FORM_NONCE);
  echo("<div class='login_form $name'>");
  echo("<div class='w3-container w3-margin-top'>");
  echo("<header class='w3-container w3-blue-gray'><h3>$header</h3></header>");
  echo("<form class='login w3-container w3-card-4' method=post action='$form_uri'>");
  echo("$nonce");
}

function close_login_form()
{
  // must close all DOM elements opened in start_login_form
  echo("</form></div></div>");
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
  echo("<!-- $label -->");
  echo("<div class='input $name'>");

  if(in_array($type,['text','password','email']))
  {
    if($kwargs['optional'] ?? False) {
      $required = 'placeholder=[optional]';
    } else {
      $required = 'required';
    }
    echo("<input class='w3-input' type=$type name=$name $required>");
    echo("<label>$label</label>");
  }  
  elseif($type == "checkbox")
  {
    $checked = ($kwargs['checked'] ?? False) ? 'checked' : '';
    echo("<input class='w3-check' type=checkbox name=$name $checked>");
    echo("<label>$label</label>");
  }
  else
  {
    log_error("Unrecognized input type ($type) passed to add_login_name");
  }

  $info = $kwargs['info'] ?? null;
  if($info)
  {
    $info_icon = '<img src='.plugin_url('img/icons8-info.png').' width=18 height=18>';
    $link = "tlc-ttsurvey-$name-info";
    echo("<a class='info-trigger' data-target=$link>$info_icon</a>");
    echo("<div id=$link class='info w3-panel w3-pale-yellow w3-border'><p>$info</p></div>");
  }

  echo("</div>");
}

function add_login_submit($label,$action,$kwargs=array())
{
  $cancel = $kwargs['cancel'] ?? False;

  $btn_classes = 'w3-button w3-section w3-ripple w3-right w3-margin-left';
  $submit_classes = "$btn_classes w3-blue-gray";
  $cancel_classes = "$btn_classes w3-light-gray";

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
