<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

function ajax_wrapper()
{
  [$key,$nonce] = $_POST['nonce'];
  if(!wp_verify_nonce($nonce,$key)) {
    log_error("Bad nonce ".__FILE__.':'.__LINE__);
    echo(json_encode(array('status'=>false, 'error'=>'bad nonce')));
    wp_die();
  }

  $query = $_POST['query'];
  $admin = $_POST['admin'] ?? false;
  [$path,$query] = explode('/',$query);
  $query_file = plugin_path("$path/ajax/$query.php");
  if(!file_exists($query_file))
  {
    log_error("Bad ajax query ($query) @ ".__FILE__.":".__LINE__);
    echo(json_encode(array(
      'status'=>false, 
      'error'=>"unimplmented query ($query)",
    )));
    wp_die();
  }

  require($query_file);
  wp_die();
}
