<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://www.mbjtechnolabs.com/
 * @since      1.0.0
 *
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/includes
 * @author     gmexsoftware <gmexsoftware@gmail.com>
 */
class Paypal_For_Edd_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
                'pal-for-edd', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}