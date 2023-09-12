<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

const LOG_FILE = 'tlc-ttsurvey.log';

$_logger_fp = null;

function logger($create=true)
{
  global $_logger_fp;

  if(is_null($_logger_fp) && $create)
  {
    $logfile = plugin_path(LOG_FILE);
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

  $file = plugin_path(LOG_FILE);
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

function dump_log_to_html()
{
  $entries = array();
  $entry_re = '/^\[(.*?)\]\s*(\w+)\s*(.*?)\s*$/';
  foreach(file(plugin_path(LOG_FILE)) as $line) {
    $m = array();
    if(preg_match($entry_re,$line,$m))
    {
      $entry = "<tr class=" . strtolower($m[2]). ">";
      $entry .= "<td class=date>" . $m[1] . "</td>";
      $entry .= "<td class=message>" . $m[3] . "</td>";
      $entry .= "</tr>";
      $entries[] = $entry;
    }
  }
  echo "<table class=log-table>";
  foreach (array_reverse($entries) as $entry) {
    echo $entry;
  }
  echo "</table>";
}

function log_dev($msg) {
  if(survey_log_level() == "DEV") {
    write_to_logger("DEV",$msg);
  }
}

function log_info($msg) {
  if(in_array(survey_log_level(),array("DEV","INFO"))) {
    write_to_logger("INFO",$msg);
  }
}

function log_warning($msg) {
  if(in_array(survey_log_level(),array("DEV","INFO","WARNING"))) {
    write_to_logger("WARNING",$msg);
  }
}

function log_error($msg) {
  write_to_logger("ERROR",$msg);
}
