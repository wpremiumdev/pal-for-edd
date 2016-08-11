<?php

if (!defined('ABSPATH')){ exit; }

function register_paypal_for_edd_advanced($gateways) {
    $gateways['paypal_for_edd_advanced'] = array(
        'admin_label' => __('PayPal Advanced', 'pal-for-edd'),
        'checkout_label' => __('PayPal Advanced', 'pal-for-edd')
    );
    return $gateways;
}

function paypal_for_edd_advanced_hide_edd_cc_fields() {
   ?>
<script type="text/javascript">
    var payment_mode = jQuery('#edd-gateway option:selected, input.edd-gateway:checked').val();
    if( 'paypal_for_edd_advanced' == payment_mode ){
        jQuery('#edd_cc_fields').remove();
    } 
</script>
       <?php
}

function paypal_for_edd_settings_section_advanced($sections) {
    $sections['paypal_advanced'] = __('PayPal Advanced', 'pal-for-edd');
    return $sections;
}

function paypal_for_edd_settings_advanced($settings) {

    $paypal_for_edd_advanced_settings = paypal_for_edd_advanced_settings();
    return array_merge($settings, $paypal_for_edd_advanced_settings);
}

function paypal_for_edd_advanced_settings() {
    $paypal_for_edd_advanced_settings = array(
        'paypal_advanced' => array(
            array(
                'id' => 'paypal_for_edd_advanced_header',
                'name' => '<strong>' . __('PayPal Advanced Settings', 'pal-for-edd') . '</strong>',
                'type' => 'header',
            ),
            
            array(
                'id' => 'paypal_for_edd_advanced_title',
                'name' => __('Title', 'pal-for-edd'),
                'type' => 'text',
                'desc' => __('This controls the title which the user sees during checkout.', 'pal-for-edd')
            ),
            array(
                'id' => 'paypal_for_edd_advanced_description',
                'name' => __('Description', 'pal-for-edd'),
                'type' => 'text',
                'desc' => __('This controls the description which the user sees during checkout.', 'pal-for-edd')
            ),
            array(
                'id' => 'paypal_for_edd_advanced_testmode',
                'name' => __('Enable Testmode ', 'pal-for-edd'),
                'type' => 'checkbox',
                'desc' => __('PayPal sandbox can be used to test payments. Sign up for a developer account <a href="https://developer.paypal.com/">here</a>', 'pal-for-edd')
            ),            
            array(
                'id' => 'paypal_for_edd_api_advanced_sandbox_merchant',
                'name' => __('Merchant Login', 'pal-for-edd'),
                'desc' => __('Your merchant login ID that you created when you registered for the account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_sandbox_password',
                'name' => __('Password', 'pal-for-edd'),
                'desc' => __('Enter your PayPal Advanced account password.', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_sandbox_user',
                'name' => __('User (or Merchant Login if no designated user is set up for the account)', 'pal-for-edd'),
                'desc' => __('Enter your PayPal Advanced user account for this site.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_sandbox_partner',
                'name' => __('PayPal Partner', 'pal-for-edd'),
                'desc' => __('Enter your PayPal Advanced Partner. If you purchased the account directly from PayPal, use PayPal.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_live_merchant',
                'name' => __('Merchant Login', 'pal-for-edd'),
                'desc' => __('Your merchant login ID that you created when you registered for the account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_live_password',
                'name' => __('Password', 'pal-for-edd'),
                'desc' => __('Enter your PayPal Advanced account password.', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_live_user',
                'name' => __('User (or Merchant Login if no designated user is set up for the account)', 'pal-for-edd'),
                'desc' => __('Enter your PayPal Advanced user account for this site.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_live_partner',
                'name' => __('PayPal Partner', 'pal-for-edd'),
                'desc' => __('Enter your PayPal Advanced Partner. If you purchased the account directly from PayPal, use PayPal.', 'pal-for-edd'),
                'type' => 'text'
            ),           
           
            array(
                'id' => 'paypal_for_edd_api_advanced_action',
                'name' => __('Payment Action ', 'pal-for-edd'),
                'type' => 'gateway_select',                
                'options' => paypal_for_edd_advanced_payment_action(),
            ),
            array(
                'id' => 'paypal_for_edd_api_advanced_layout',
                'name' => __('Layout ', 'pal-for-edd'),
                'type' => 'gateway_select',
                'desc' => __('Layouts A and B redirect to PayPal\'s website for the user to pay.<br> Layout C (recommended) is a secure PayPal-hosted page but is embedded on your site using an iFrame.', 'pal-for-edd'),
                'options' => paypal_for_edd_advanced_payment_layout(),
            ),
            array(
                'id' => 'paypal_for_edd_advanced_mobile_mode',
                'name' => __('Mobile Mode ', 'pal-for-edd'),
                'desc' => __('Disable this option if your theme is not compatible with Mobile. Otherwise You would get Silent Post Error in Layout C.', 'pal-for-edd'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'paypal_for_edd_advanced_invoice_prefix',
                'name' => __('Invoice Prefix', 'pal-for-edd'),                
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_advanced_page_collapse_bgcolor',
                'name' => __('Page Collapse Border Color', 'pal-for-edd'),                     
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_advanced_page_collapse_textcolor',
                'name' => __('Page Collapse Text Color', 'pal-for-edd'),                    
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_advanced_page_button_bgcolor',
                'name' => __('Page Button Background Color', 'pal-for-edd'),                  
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_advanced_page_button_textcolor',
                'name' => __('Page Button Text Color', 'pal-for-edd'),                 
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_advanced_label_textcolor',
                'name' => __('Label Text Color', 'pal-for-edd'),                  
                'type' => 'text',
                
            ),
            array(
                'id' => 'paypal_for_edd_advanced_debug',
                'name' => __('Debug Log', 'pal-for-edd'),
                'desc' => sprintf(__('Enable logging <code>%s</code>', 'pal-for-edd'), paypal_for_edd_log_file_path('paypal_for_edd_paypal_advanced')),
                'type' => 'checkbox'
            )
        ),
    );
    return $paypal_for_edd_advanced_settings;
}

function paypal_for_edd_advanced_payment_action() {

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

function paypal_for_edd_advanced_payment_layout() {

    $action = array(
        'A' => array(
            'admin_label' => __('Layout A', 'pal-for-edd')
        ),
        'B' => array(
            'admin_label' => __('Layout B', 'pal-for-edd')
        ),
        'c' => array(
            'admin_label' => __('Layout C', 'pal-for-edd')
        )
    );

    return apply_filters('edd_payment_action', $action);
}

function paypal_for_edd_advanced_process_payment($purchase_data) {

    $Paypal_For_EDD_PayPal_Advanced_Helper = new Paypal_For_EDD_PayPal_Advanced_Helper();
    $Paypal_For_EDD_PayPal_Advanced_Helper->paypal_for_edd_paypal_advanced_process_payment($purchase_data);
}

if (!function_exists('paypal_for_edd_log_file_path')) {

    function paypal_for_edd_log_file_path($handle) {
        return trailingslashit(PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR) . $handle . '_' . sanitize_file_name(wp_hash($handle)) . '.log';
    }

}