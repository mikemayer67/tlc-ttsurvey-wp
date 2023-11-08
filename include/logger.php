<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');

$_logger_fp = null;

function logger($create=true)
{
  global $_logger_fp;

  if(is_null($_logger_fp) && $create)
  {
    $logfile = plugin_path(PLUGIN_LOG_FILE);
    if( file_exists($logfile) and filesize($logfile) > 512*1024 ) {
      $tempfile = $logfile.".tmp";
      $_logger_fp = fopen($tempfile,"w");
      $skip = 1000;
      foreach(file($logfile) as $line) {
        if($skip > 0) {
          $skip--;
        } else {
          fwrite($_logger_fp,$line);
        }
      }
      fclose($_logger_fp);
      unlink($logfile);
      rename($tempfile,$logfile);
    }
    $_logger_fp = fopen($logfile,"a");
  }
  return $_logger_fp;
}

function clear_logger()
{
  global $_logger_fp;
  log_dev("clear_logger: $_logger_fp");

  $file = plugin_path(PLUGIN_LOG_FILE);
  if($_logger_fp) { 
    fclose($_logger_fp); 
    unlink($file);
  }
  $_logger_fp = fopen($file,"a");
  log_info("log cleared");
}


function write_to_logger($prefix,$msg)
{
  $datetime = current_datetime();
  $timestamp = $datetime->format("d-M-y H:i:s.v T");
  $prefix = str_pad($prefix,8);
  fwrite(logger(), "[{$timestamp}] {$prefix} {$msg}\n");
}

/**
 * log_dev is intended to only be useful during development debugging
 */
function log_dev($msg) {
  if(survey_log_level() == "DEV") {
    write_to_logger("DEV",$msg);
  }
}

/**
 * log_info is intended to show normal flow through the plugin code
 **/
function log_info($msg) {
  if(in_array(survey_log_level(),array("DEV","INFO"))) {
    write_to_logger("INFO",$msg);
  }
}

/**
 * log_warning is intended to show abnormal, but not necessarily
 *   critical flows through the plugin code
 */
function log_warning($msg) {
  if(in_array(survey_log_level(),array("DEV","INFO","WARNING"))) {
    write_to_logger("WARNING",$msg);
  }
}

/**
 * log_error is intended to show critical errors in the plugin code
 **/
function log_error($msg) {
  write_to_logger("ERROR",$msg);
  error_log(plugin_name().": $msg");
}
