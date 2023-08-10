<?php
namespace TLC\TTSurvey;

/**
 * Setup and querying of plugin database tables
 */

if( ! defined('WPINC') ) { die; }

require_once 'logger.php';

function participant_table()
{
  global $wpdb;
  return $wpdb->prefix . "tlc_ttsurvey_participants";
}

function structure_table()
{
  global $wpdb;
  return $wpdb->prefix . "tlc_ttsurvey_structure";
}

function tlc_db_activate()
{
  global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE " . participant_table() . " (
    id char(6) NOT NULL,
    name varchar(255) NOT NULL,
    email varchar(255) DEFAULT NULL,
    data LONGTEXT,
    PRIMARY KEY  (id),
    UNIQUE KEY key_UNIQUE (id)
  ) $charset_collate;";
	dbDelta($sql);

	$sql = "CREATE TABLE " . structure_table() . " (
    year char(4) NOT NULL,
    data LONGTEXT,
    PRIMARY KEY  (year),
    UNIQUE KEY year_UNIQUE (year)
  ) $charset_collate;";
  dbDelta($sql);
}

function survey_years()
{
  global $wpdb;
  $sql = "SELECT year from " . structure_table() . ";";
  $sql = $wpdb->prepare($sql);
  $years = $wpdb->get_col($sql);
  return $years ?? array();
}
