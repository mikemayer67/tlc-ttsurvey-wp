<?php
namespace TLC\TTSurvey;

/**
 * Setup and querying of plugin database tables
 */

if( ! defined('WPINC') ) { die; }

require_once 'logger.php';

function tlc_db_activate()
{
  global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$pre = $wpdb->prefix . "tlc_ttsurvey";
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE ${pre}_participants (
    id char(6) NOT NULL,
    name varchar(255) NOT NULL,
    email varchar(255) DEFAULT NULL,
    data LONGTEXT,
    PRIMARY KEY  (id),
    UNIQUE KEY key_UNIQUE (id)
  ) $charset_collate;";
	dbDelta($sql);

  $sql = "CREATE TABLE ${pre}_structure (
    year char(4) NOT NULL,
    data LONGTEXT,
    PRIMARY KEY  (year),
    UNIQUE KEY year_UNIQUE (year)
  ) $charset_collate;";
  dbDelta($sql);
}
