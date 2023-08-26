<?php
namespace TLC\TTSurvey;

/**
 * Setup and handling of plugin settings
 *
 * The plugin settings are stored as a single json encoded dictionary in the WP options database.
 * It contains the following fields (which may or may not be present):
 *
 */

if( ! defined('WPINC') ) { die; }

const OPTIONS_KEY = 'tlc_ttsurvey_options';

const CAPS_KEY = 'caps';
const ACTIVE_YEAR_KEY = 'active_year';
const PDF_URI_KEY = 'pdf_href';

const OPTION_DEFAULTS = array(
  CAPS_KEY => [],
  ACTIVE_YEAR_KEY => '',
  PDF_URI_KEY => '',
);

class Settings
{
  /**
   * singleton instance
   */
  private static $_instance = null;

  /**
   * values dictionary
   */

  private $_values = array();

  /**
   * return singleton instanceo
   */
  static function instance() {
    if( self::$_instance == null ) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * (private) constructor
   *
   * Instantiates values from the WP database
   */
  private function __construct() {
    $options = get_option(OPTIONS_KEY,null);
    if( isset($options) ) {
        $this->_values = array_replace($this->_values, $options);
    }
  }

  /**
   * get active survey year
   * @return active survey year
   */
  static function active_year() {
    return self::instance()->get(ACTIVE_YEAR_KEY);
  }

  /**
   * get URI for pdf of the survey
   * @return uri for link to pdf of the current survey
   */
  static function pdf_uri() {
    return self::instance()->get(PDF_URI_KEY);
  }


  /**
   * get option value
   *
   * Returns null if the option isn't currently set
   *
   * @param string $key option key to retrieve
   * @return string or null
   */
  public function get($key) {
    return $this->_values[$key] ?? (OPTION_DEFAULTS[$key] ?? null);
  }

  /**
   * set option value
   *
   * Can only be used as admin
   *
   * @param string $key option key to set
   * @param mixed $value option value to set
   */
  public function set($key,$value) {
    if( ! is_admin() ) { return; }
    $this->_values[$key] = $value;
    update_option(OPTIONS_KEY,$this->_values);
  }

  /**
   * convert values to json string
   *
   * @return string 
   */
  public function __toString() {
    return json_encode($this->_values);
  }

  /**
   * reset option value
   *
   * Can only be used as admin
   *
   * Returns option to default value if one exists
   * Clears option if no default value exists
   *
   * Resets all options to default values if no key is specified
   *
   * @param optional string $key option key to reset
   */
  function reset($key=null) {
    if( ! is_admin() ) { return; }

    if( empty($key) ) {
      $this->_vaues = array();
    } else {
      unset($this->_values[$key]);
    }

    update_option(OPTIONS_KEY,$this->_values);
  }

  /**
   * removes plugin settings from the WP database
   */
  static function uninstall()
  {
    delete_option(OPTIONS_KEY);
  }

  /**
   * updates settings from update post
   */
  function update_from_post($post)
  {
    if (!wp_verify_nonce($post['_wpnonce'],SETTINGS_NONCE)) {
      log_error("failed to validate nonce");
      wp_die("Bad nonce");
    }

    $this->set(ACTIVE_YEAR_KEY, $post['active_year']);

    $new_caps = $post['caps'];
    $this->set(CAPS_KEY,$new_caps);

    $new_pdf_uri = $post['pdf_uri'];
    log_info("new_pdf_uri: $new_pdf_uri");
    $new_pdf_uri = sanitize_url($new_pdf_uri,['http','https','ftp','ftps']);
    log_info("(sanitized): $new_pdf_uri");
    $this->set(PDF_URI_KEY,$new_pdf_uri);

    $all_users = get_users();
    foreach($all_users as $user) {
      $id = $user->id;
      foreach(['responses','structure'] as $cap) {
        $key = "tlc-ttsurvey-$cap";
        if($new_caps[$cap][$id]) {
          $user->add_cap($key);
        } else {
          $user->remove_cap($key);
        }
      }
    }
  }

};



