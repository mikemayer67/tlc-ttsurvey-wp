<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

const LOG_FILE = 'tlc-ttsurvey.log';

$logger_fp = null;

function init_logger()
{
  if(is_null($logger_fp))
  {
    $logfile = plugin_path(LOG_FILE);
    if( file_exists($logfile) and filesize($logfile) > 512*1024 ) {
      $tempfile = $logfile.".tmp";
      $fp = fopen($tempfile,"w");
      $skip = 1000;
      foreach(file($logfile) as $line) {
        if($skip > 0) {
          $skip--;
        } else {
          fwrite($fp,$line);
        }
      }
      fclose($fp);
      unlink($logfile);
      rename($tempfile,$logfile);
    }
    $logger_fp = fopen($logfile,"a");
  }
}

function write_to_logger($prefix,$msg)
{
  init_logger();
  $datetime = new \DateTime;
  $timestamp = $datetime->format("d-M-y H:i:s.v e");
  $prefix = str_pad($prefix,8);
  fwrite($logger_fp, "[{$timestamp}] {$prefix} {$msg}\n");
}

function dump_log_to_html($level="INFO")
{
  init_logger();
  if($level=="ERROR") {
    $levels = ["ERROR"];
  } elseif($level=="WARNING") {
    $levels = ["WARNING","ERROR"];
  } else {
    $levels = ["INFO","WARNING","ERROR"];
  }

  $entries = array();
  $entry_re = '/^\[(.*?)\]\s*(\w+)\s*(.*?)\s*$/';
  foreach(file(plugin_path(LOG_FILE)) as $line) {
    $m = array();
    if(preg_match($entry_re,$line,$m))
    {
      if(in_array($m[2],$levels)) {
        $entry = "<tr class=" . strtolower($m[2]). ">";
        $entry .= "<td class=date>" . $m[1] . "</td>";
        $entry .= "<td class=message>" . $m[3] . "</td>";
        $entry .= "</tr>";
        $entries[] = $entry;
      }
    }
  }
  echo "<table class=log-table>";
  foreach (array_reverse($entries) as $entry) {
    echo $entry;
  }
  echo "</table>";
}

function clear_logger()
{
  init_logger();
  $file = plugin_path(LOG_FILE);
  fclose($logger_fp);
  unlink($file);
  $logger_fp = fopen($file,"a");
}
}

function log_dev($msg) {
  write_to_logger("DEV",$msg);
}

function log_info($msg) {
  write_to_logger("INFO",$msg);
}

function log_warning($msg) {
  write_to_logger("WARNING",$msg);
}

function log_error($msg) {
  write_to_logger("ERROR",$msg);
}
