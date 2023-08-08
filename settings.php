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

const OPTION_DEFAULTS = [
  CAPS_KEY => [

  ],
];

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
      try {
        $this->_values = array_replace($this->_values, $options);
      } catch (TypeError $e) {
      } catch (Exception $e) {
      }
    }
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

};



