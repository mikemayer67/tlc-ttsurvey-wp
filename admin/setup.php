<?php
namespace TLC\TTSurvey;

/**
 * Setup and handling of the settings page in the admin backend
 */

if( ! defined('WPINC') ) { die; }
if( ! is_admin() ) { return; }

const OPTIONS_NONCE = 'tlc-ttsurvey-settings';
const SETTINGS_PAGE_SLUG = 'tlc-ttsurvey-settings';
const LOG_PAGE_SLUG = 'tlc-ttsurvey-log';

require_once plugin_path('include/logger.php');
require_once plugin_path('settings.php');

function handle_admin_init()
{
  wp_enqueue_style('tlc-ttsurvey-admin', plugin_url('css/tlc-ttsurvey-admin.css'));

  #add_javascript goes here
}

function handle_admin_menu()
{
  add_options_page(
    'Time & Talent Survey', // page title
    'Time & Talent Survey', // menu title
    'manage_options', // required capability
    SETTINGS_PAGE_SLUG, // settings page slug
    ns('populate_settings_page'), // callback to populate settingsn page
  );
}

function add_settings_link($links)
{
  $options_url = admin_url('options-general.php');
  $options_url .= "?page=".SETTINGS_PAGE_SLUG;
  $settings_url = $options_url . "&tab=settings";
  $log_url = $options_url . "&tab=log";
  array_unshift($links,"<a href='$log_url'>Log</a>");
  array_unshift($links,"<a href='$settings_url'>Settings</a>");
  return $links;
}

$action_links = 'plugin_action_links_' . plugin_basename(plugin_file());

add_action('admin_menu',  ns('handle_admin_menu'));
#add_action('admin_init', ns('handle_admin_init'));
add_action('init',        ns('handle_admin_init'));
add_action($action_links, ns('add_settings_link'));

function populate_settings_page()
{
  if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

  echo "<div class=wrap>";
  require plugin_path('admin/plugin_page.php');
  echo "</div>";
}
