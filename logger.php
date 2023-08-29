<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

const LOG_FILE = 'tlc-ttsurvey.log';

class Logger
{
  private static $_instance = null;

  static function instance() {
    if( self::$_instance == null ) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }


  private function __construct() {
    $logger = plugin_path(LOG_FILE);
    if( file_exists($logger) and filesize($logger) > 512*1024 ) {
      $tempfile = $logger.".tmp";
      $fp = fopen($tempfile,"w");
      $skip = 1000;
      foreach(file($logger) as $line) {
        if($skip > 0) {
          $skip--;
        } else {
          fwrite($fp,$line);
        }
      }
      fclose($fp);
      unlink($logger);
      rename($tempfile,$logger);
    }
    $this->fp = fopen($logger,"a");
  }

  function __destruct() {
    fclose($this->fp);
  }


  function add($prefix,$msg) {
    $datetime = new \DateTime;
    $timestamp = $datetime->format("d-M-y H:i:s.v e");
    $prefix = str_pad($prefix,8);
    fwrite($this->fp, "[{$timestamp}] {$prefix} {$msg}\n");
  }

  function dump_html($level="INFO")
  {
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

  function clear()
  {
    $file = plugin_path(LOG_FILE);
    fclose($this->fp);
    unlink($file);
    $this->fp = fopen($file,"a");
  }
}

function log_dev($msg) {
  Logger::instance()->add("DEV",$msg);
}

function log_info($msg) {
  Logger::instance()->add("INFO",$msg);
}

function log_warning($msg) {
  Logger::instance()->add("WARNING",$msg);
}

function log_error($msg) {
  Logger::instance()->add("ERROR",$msg);
}
