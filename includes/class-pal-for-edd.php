<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.mbjtechnolabs.com/
 * @since      1.0.0
 *
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Paypal_For_Edd
 * @subpackage Paypal_For_Edd/includes
 * @author     gmexsoftware <gmexsoftware@gmail.com>
 */
class Paypal_For_Edd {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Paypal_For_Edd_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'pal-for-edd';
        $this->version = '1.0.5';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

        add_action('parse_request', array($this, 'handle_api_requests'), 0);        
        add_action('paypal_for_edd_api_paypalsetexpresscheckout', array($this, 'paypal_for_edd_api_paypalsetexpresscheckout'));
        add_action('paypal_for_edd_api_eddpaypaladvanced', array($this, 'paypal_for_edd_api_eddpaypaladvanced'));        
        add_action('paypal_for_edd_api_paypalplaceorder', array($this, 'paypal_for_edd_api_paypalplaceorder'));
        add_action('http_api_curl', array($this, 'http_api_curl_ex_add_curl_parameter'), 10, 3);
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Paypal_For_Edd_Loader. Orchestrates the hooks of the plugin.
     * - Paypal_For_Edd_i18n. Defines internationalization functionality.
     * - Paypal_For_Edd_Admin. Defines all hooks for the admin area.
     * - Paypal_For_Edd_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pal-for-edd-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pal-for-edd-logger.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pal-for-edd-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-pal-for-edd-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-pal-for-edd-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/pal-for-edd-public-display.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-express-checkout.php';
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-pro.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-express-checkout-helper.php';
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-pro-helper.php';
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-payflow-helper.php';
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-payflow.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-advanced.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/class-pal-for-edd-paypal-advanced-helper.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/gatways/lib/paypal.class.php';

        $this->loader = new Paypal_For_Edd_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Paypal_For_Edd_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Paypal_For_Edd_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Paypal_For_Edd_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        /**
         * PayPal Express Checkout
         */        
        add_filter('edd_payment_gateways', 'register_paypal_for_edd');
        add_filter('edd_settings_sections_gateways', 'paypal_for_edd_settings_section');
        add_filter('edd_settings_gateways', 'paypal_for_edd_settings');
        
        /**
         * PayPal Pro
         */        
        add_filter('edd_payment_gateways', 'register_paypal_for_edd_pro');
        add_filter('edd_settings_sections_gateways', 'paypal_for_edd_settings_section_pro');
        add_filter('edd_settings_gateways', 'paypal_for_edd_settings_pro');       
        
        /**
         * PayPal Pro
         */        
        add_filter('edd_payment_gateways', 'register_paypal_for_edd_payflow');
        add_filter('edd_settings_sections_gateways', 'paypal_for_edd_settings_section_payflow');
        add_filter('edd_settings_gateways', 'paypal_for_edd_settings_payflow');

        /**
         * PayPal Advanced
         */
        add_filter('edd_payment_gateways', 'register_paypal_for_edd_advanced');
        add_filter('edd_settings_sections_gateways', 'paypal_for_edd_settings_section_advanced');
        add_filter('edd_settings_gateways', 'paypal_for_edd_settings_advanced');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Paypal_For_Edd_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        /**
         * PayPal Express Checkout
         */        
        add_action('edd_paypal_for_edd_cc_form', 'paypal_for_edd_display_testmode');
        add_action('edd_gateway_paypal_for_edd', 'paypal_for_edd_process_payment');
        add_action('edd_checkout_form_top', 'show_button_checkout');
        add_action('edd_checkout_form_top', 'show_full_shipping_details');
        
        /**
         * PayPal Pro
         */        
        add_action('edd_gateway_paypal_for_edd_payflow', 'paypal_for_edd_payflow_process_payment');
        
        /**
         * PayPal PayFlow
         */        
        add_action('edd_gateway_paypal_for_edd_pro', 'paypal_for_edd_pro_process_payment');

        /**
         * PayPal Advanced
         */
        add_action('edd_gateway_paypal_for_edd_advanced', 'paypal_for_edd_advanced_process_payment');
        add_action('edd_after_cc_fields', 'paypal_for_edd_advanced_hide_edd_cc_fields');
    }

    public function handle_api_requests() {

        global $wp;
        if (isset($_GET['action']) && $_GET['action'] == 'ipn_handler') {
            $wp->query_vars['Paypal_For_Edd'] = $_GET['action'];
        }
        if (isset($_GET['payment-confirmation']) && $_GET['payment-confirmation'] == 'PayPalSetExpressCheckout') {
            $wp->query_vars['PayPal_For_EDD'] = sanitize_text_field($_GET['payment-confirmation']);
        }
        if (isset($_GET['payment-confirmation']) && $_GET['payment-confirmation'] == 'PayPalPlaceOrder') {
            $wp->query_vars['PayPal_For_EDD'] = sanitize_text_field($_GET['payment-confirmation']);
        }
        if (isset($_GET['edd-advanced-api']) && $_GET['edd-advanced-api'] == 'EDDPaypalAdvanced') {
            $wp->query_vars['PayPal_For_EDD'] = sanitize_text_field($_GET['edd-advanced-api']);
        }
        if (!empty($wp->query_vars['PayPal_For_EDD'])) {
            ob_start();
            $api = strtolower(esc_attr($wp->query_vars['PayPal_For_EDD']));
            do_action('paypal_for_edd_api_' . strtolower($api));
            ob_end_clean();
            die('1');
        }
    }

    public function paypal_for_edd_api_paypalsetexpresscheckout() {

        $Paypal_For_EDD_Express_Checkout_Helper = new Paypal_For_EDD_Express_Checkout_Helper();
        $Paypal_For_EDD_Express_Checkout_Helper->paypal_express_checkout_GetShippingDetails($_GET);
    }

    public function paypal_for_edd_api_paypalplaceorder() {
        $Paypal_For_EDD_Express_Checkout_Helper = new Paypal_For_EDD_Express_Checkout_Helper();
        $Paypal_For_EDD_Express_Checkout_Helper->Do_Payment_Confirm('', '', '');
    }

    public function paypal_for_edd_api_eddpaypaladvanced() {

        $Paypal_For_EDD_PayPal_Advanced_Helper = new Paypal_For_EDD_PayPal_Advanced_Helper();
        $Paypal_For_EDD_PayPal_Advanced_Helper->paypal_for_edd_paypal_advanced_response($_GET);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Paypal_For_Edd_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
     public function http_api_curl_ex_add_curl_parameter($handle, $r, $url ) {
        if ( strstr( $url, 'https://' ) && strstr( $url, '.paypal.com' ) ) {
            curl_setopt($handle, CURLOPT_VERBOSE, 1);
            curl_setopt($handle, CURLOPT_SSLVERSION, 6);
        }
    }
}