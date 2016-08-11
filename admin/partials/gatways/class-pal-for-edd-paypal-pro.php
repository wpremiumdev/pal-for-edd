<?php

if (!defined('ABSPATH'))
    exit;

function register_paypal_for_edd_pro($gateways) {
    $gateways['paypal_for_edd_pro'] = array(
        'admin_label' => __('PayPal Pro', 'pal-for-edd'),
        'checkout_label' => __('PayPal Pro', 'pal-for-edd')
    );
    return $gateways;
}

function paypal_for_edd_settings_section_pro($sections) {
    $sections['paypal_pro'] = __('PayPal Pro', 'pal-for-edd');
    return $sections;
}

function paypal_for_edd_settings_pro($settings) {

    $paypal_for_edd_pro_settings = paypal_for_edd_pro_settings();
    return array_merge($settings, $paypal_for_edd_pro_settings);
}

function paypal_for_edd_pro_settings() {
    $paypal_for_edd_pro_settings = array(
        'paypal_pro' => array(
            array(
                'id' => 'paypal_for_edd_pro_sandbox_credentials',
                'name' => '<strong>' . __('PayPal Pro Settings', 'pal-for-edd') . '</strong>',
                'type' => 'header',
            ),
            array(
                'id' => 'paypal_for_edd_pro_testmode',
                'name' => __('Enable Testmode ', 'pal-for-edd'),
                'type' => 'checkbox',
                'desc' => __('Enable Paypal Pro Sandbox/Test Mode', 'pal-for-edd')
            ),
            array(
                'id' => 'paypal_for_edd_api_pro_sandbox_username',
                'name' => __('API Username ', 'pal-for-edd'),
                'desc' => sprintf(__('Create sandbox accounts and obtain API credentials from within your <a href="%s" target="_blank">PayPal developer account</a>.', 'credit-card-payment'), 'https://developer.paypal.com/'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_pro_sandbox_password',
                'name' => __('API Password ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_pro_sandbox_signature',
                'name' => __('API Signature ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_pro_live_username',
                'name' => __('API Username ', 'pal-for-edd'),
                'desc' => __('Get your live account API credentials from your PayPal account profile under the API Access section or by using <a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_login-api-run" target="_blank">here.</a>', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_pro_live_password',
                'name' => __('API Password ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_pro_live_signature',
                'name' => __('API Signature ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_pro_invoice_id_prefix',
                'name' => __('Invoice ID Prefix', 'pal-for-edd'),
                'desc' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_pro_debug',
                'name' => __('Debug Log', 'pal-for-edd'),
                'desc' => sprintf(__('Enable logging <code>%s</code>', 'pal-for-edd'), paypal_for_edd_log_file_path('paypal_for_edd_paypal_pro')),
                'type' => 'checkbox'
            )
        ),
    );
    return $paypal_for_edd_pro_settings;
}

function paypal_for_edd_pro_process_payment($purchase_data) {

    $Paypal_For_EDD_PayPal_Pro_Helper = new Paypal_For_EDD_PayPal_Pro_Helper();
    $Paypal_For_EDD_PayPal_Pro_Helper->paypal_for_edd_paypal_pro_process_payment($purchase_data);
}

if (!function_exists('paypal_for_edd_log_file_path')) {

    function paypal_for_edd_log_file_path($handle) {
        return trailingslashit(PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR) . $handle . '_' . sanitize_file_name(wp_hash($handle)) . '.log';
    }

}