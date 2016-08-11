<?php

/**
 * @link              http://www.mbjtechnolabs.com/
 * @since             1.0.0
 * @package           Paypal_For_Edd
 *
 * @wordpress-plugin
 * Plugin Name:       PayPal For Easy Digital Downloads (EDD)
 * Plugin URI:        pal-for-edd
 * Description:       PayPal For Easy Digital Downloads (EDD)
 * Version:           1.0.5
 * Author:            gmexsoftware
 * Author URI:        http://www.mbjtechnolabs.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pal-for-edd
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
if (!defined('EDD_EX_PLUGIN_PLUGIN_URL')) {
    define('EDD_EX_PLUGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR')) {
    $upload_dir = wp_upload_dir();
    define('PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR', $upload_dir['basedir'] . '/pal-for-edd-logs/');
}
if (!defined('PAYPAL_FOR_EDD_PLUGIN_DIR_PATH')) {
    define('PAYPAL_FOR_EDD_PLUGIN_DIR_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pal-for-edd-activator.php
 */
function activate_paypal_for_edd() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pal-for-edd-activator.php';
    Paypal_For_Edd_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pal-for-edd-deactivator.php
 */
function deactivate_paypal_for_edd() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pal-for-edd-deactivator.php';
    Paypal_For_Edd_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_paypal_for_edd');
register_deactivation_hook(__FILE__, 'deactivate_paypal_for_edd');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pal-for-edd.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_paypal_for_edd() {

    $plugin = new Paypal_For_Edd();
    $plugin->run();
}
run_paypal_for_edd();