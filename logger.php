<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

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
    $logfile = plugin_path('tlc-ttsurvey.log');
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
    $this->fp = fopen($logfile,"a");
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
