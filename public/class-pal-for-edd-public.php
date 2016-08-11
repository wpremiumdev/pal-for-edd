<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.mbjtechnolabs.com/
 * @since      1.0.0
 *
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/public
 * @author     gmexsoftware <gmexsoftware@gmail.com>
 */
class Paypal_For_Edd_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->pal_for_edd_is_order_pay();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Paypal_For_Edd_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Paypal_For_Edd_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pal-for-edd-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Paypal_For_Edd_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Paypal_For_Edd_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pal-for-edd-public.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . 'paypal_for_edd_blockUI', plugin_dir_url(__FILE__) . 'js/pal-for-edd-public-blockUI.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'paypal_for_edd_checkout', apply_filters('paypal_for_edd_checkout', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'paypal_for_edd_check_out' => wp_create_nonce("paypal_for_edd_check_out"),
        )));

        wp_localize_script($this->plugin_name, 'base_url', EDD_EX_PLUGIN_PLUGIN_URL);
    }

    public function pal_for_edd_is_order_pay() {
        try {

            if (isset($_GET['order-pay']) && !empty($_GET['order-pay'])) {
                add_shortcode('download_checkout', array($this, 'pal_for_edd_is_order_pay_hook'));
            }
        } catch (Exception $ex) {
            
        }
    }

    public function pal_for_edd_is_order_pay_hook() {
        try {
            if( (isset($_GET['order-pay']) && !empty($_GET['order-pay'])) && (isset($_GET['key']) && !empty($_GET['key'])) ){
                pay_for_edd_get_template('class-shortcode-checkout.php');
            }
        } catch (Exception $ex) {
            
        }
    }
}