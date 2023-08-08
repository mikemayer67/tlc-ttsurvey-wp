<?php
namespace TLC\TTSurvey;

/**
 * Setup and handling of the settings page in the admin backend
 */

if( ! defined('WPINC') ) { die; }
if( ! is_admin() ) { return; }

require_once 'logger.php';
require_once 'settings.php';

const SETTINGS_NONCE = 'tlc-ttsurvey-settings';
const SETTINGS_PAGE_SLUG = 'tlc-ttsurvey-settings';

function handle_init()
{
  wp_enqueue_style('tlc-ttsurvey', plugin_url('css/tlc-ttsurvey.css'));

  #add_javascript goes here
}

#function handle_admin_init()
#{
#}

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
  $overview_url = $options_url . "&tab=overview";
  log_info("Options URL: $options_url");
  array_unshift($links,"<a href='$settings_url'>Settings</a>");
  array_unshift($links,"<a href='$overview_url'>Overview</a>");
  return $links;
}

$action_links = 'plugin_action_links_' . plugin_basename(plugin_file());

add_action('admin_menu',  ns('handle_admin_menu'));
#add_action('admin_init', ns('handle_admin_init'));
add_action('init',        ns('handle_init'));
add_action($action_links, ns('add_settings_link'));

function populate_settings_page()
{
  require 'admin/settings.php';
}
