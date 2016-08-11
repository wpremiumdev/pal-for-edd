<?php

if (!defined('ABSPATH'))
    exit;

function register_paypal_for_edd_payflow($gateways) {
    $gateways['paypal_for_edd_payflow'] = array(
        'admin_label' => __('PayPal Payflow', 'pal-for-edd'),
        'checkout_label' => __('PayPal Payflow', 'pal-for-edd')
    );
    return $gateways;
}

function paypal_for_edd_settings_section_payflow($sections) {
    $sections['paypal_payflow'] = __('PayPal Payflow', 'pal-for-edd');
    return $sections;
}

function paypal_for_edd_settings_payflow($settings) {

    $paypal_for_edd_payflow_settings = paypal_for_edd_payflow_settings();
    return array_merge($settings, $paypal_for_edd_payflow_settings);
}

function paypal_for_edd_payflow_settings() {
    $paypal_for_edd_payflow_settings = array(
        'paypal_payflow' => array(
            array(
                'id' => 'paypal_for_edd_payflow_sandbox_credentials',
                'name' => '<strong>' . __('PayPal Payflow Settings', 'pal-for-edd') . '</strong>',
                'type' => 'header',
            ),
            array(
                'id' => 'paypal_for_edd_payflow_testmode',
                'name' => __('Enable Testmode ', 'pal-for-edd'),
                'type' => 'checkbox',
                'desc' => __('Enable Paypal Payflow Sandbox/Test Mode', 'pal-for-edd')
            ),
            array(
                'id' => 'paypal_for_edd_api_Payflow_sandbox_vendor',
                'name' => __('PayPal Vendor', 'pal-for-edd'),
                'desc' => __('Your merchant login ID that you created when you registered for the account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_sandbox_password',
                'name' => __('PayPal Password', 'pal-for-edd'),
                'desc' => __('The password that you defined while registering for the account.', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_sandbox_user',
                'name' => __('PayPal User', 'pal-for-edd'),
                'desc' => __('If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise, leave this field blank.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_sandbox_partner',
                'name' => __('PayPal Partner', 'pal-for-edd'),
                'desc' => __('The ID provided to you by the authorized PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_Payflow_live_vendor',
                'name' => __('PayPal Vendor', 'pal-for-edd'),
                'desc' => __('Your merchant login ID that you created when you registered for the account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_live_password',
                'name' => __('PayPal Password', 'pal-for-edd'),
                'desc' => __('The password that you defined while registering for the account.', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_live_user',
                'name' => __('PayPal User', 'pal-for-edd'),
                'desc' => __('If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise, leave this field blank.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_live_partner',
                'name' => __('PayPal Partner', 'pal-for-edd'),
                'desc' => __('The ID provided to you by the authorized PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_payflow_live_action',
                'name' => __('Payment Action ', 'pal-for-edd'),
                'type' => 'gateway_select',
                'desc' => __('Sale will capture the funds immediately when the order is placed. Authorization will authorize the payment but will not capture the funds. You would need to capture funds through your PayPal account when you are ready to deliver.', 'pal-for-edd'),
                'options' => paypal_for_edd_payflow_payment_action(),
            ),
            array(
                'id' => 'paypal_for_edd_payflow_invoice_id_prefix',
                'name' => __('Invoice ID Prefix', 'pal-for-edd'),
                'desc' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_payflow_debug',
                'name' => __('Debug Log', 'pal-for-edd'),
                'desc' => sprintf(__('Enable logging <code>%s</code>', 'pal-for-edd'), paypal_for_edd_log_file_path('paypal_for_edd_paypal_payflow')),
                'type' => 'checkbox'
            )
        ),
    );
    return $paypal_for_edd_payflow_settings;
}

function paypal_for_edd_payflow_payment_action() {

    $action = array(
        'Sale' => array(
            'admin_label' => __('Sale', 'pal-for-edd')
        ),
        'Authorization' => array(
            'admin_label' => __('Authorization', 'pal-for-edd')
        )
    );

    return apply_filters('edd_payment_action', $action);
}

function paypal_for_edd_payflow_process_payment($purchase_data) {

    $Paypal_For_EDD_PayPal_Payflow_Helper = new Paypal_For_EDD_PayPal_Payflow_Helper();
    $Paypal_For_EDD_PayPal_Payflow_Helper->paypal_for_edd_paypal_Payflow_process_payment($purchase_data);
}

if (!function_exists('paypal_for_edd_log_file_path')) {

    function paypal_for_edd_log_file_path($handle) {
        return trailingslashit(PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR) . $handle . '_' . sanitize_file_name(wp_hash($handle)) . '.log';
    }

}