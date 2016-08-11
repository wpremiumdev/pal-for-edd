<?php
if (!defined('ABSPATH'))
    exit;

function register_paypal_for_edd($gateways) {
    $gateways['paypal_for_edd'] = array(
        'admin_label' => __('PayPal Express Checkout', 'pal-for-edd'),
        'checkout_label' => __('PayPal Express Checkout', 'pal-for-edd')
    );
    return $gateways;
}

function paypal_for_edd_display_testmode() {
    global $edd_options;

    $test_mode = ( $test_mode = ( edd_is_test_mode() ) ? 1 : ( isset($edd_options['paypal_for_edd_ex_testmode']) ? 1 : 0 ) );
    if ($test_mode) {
        echo wpautop('<div style="color: #00529B;background-color: #BDE5F8;padding: 10px;margin-bottom: 10px;"><strong>' . __('Please Note: This transaction will run on TESTMODE.', 'pal-for-edd') . '</strong></div> ');
    } else {
        echo wpautop('<div style="color: #00529B;background-color: #BDE5F8;padding: 10px;margin-bottom: 10px;"><strong>' . __('Please Note: This transaction will run on LIVE.', 'pal-for-edd') . '</strong></div> ');
    }
}

function paypal_for_edd_settings_section($sections) {
    $sections['paypal_express_checkout'] = __('PayPal Express Checkout', 'pal-for-edd');
    return $sections;
}

function paypal_for_edd_settings($settings) {

    $paypal_for_edd_express_checkout_settings = paypal_for_edd_express_checkout_settings();
    return array_merge($settings, $paypal_for_edd_express_checkout_settings);
}

function paypal_for_edd_express_checkout_settings() {

    $paypal_for_edd_express_checkout_settings = array(
        'paypal_express_checkout' => array(
            array(
                'id' => 'paypal_for_edd_ex_sandbox_credentials',
                'name' => '<strong>' . __('Express Checkout Settings', 'pal-for-edd') . '</strong>',
                'type' => 'header',
            ),
            array(
                'id' => 'paypal_for_edd_ex_testmode',
                'name' => __('Enable Testmode ', 'pal-for-edd'),
                'desc' => __('The testmode is PayPal\'s test environment and is only for use with sandbox accounts created within your <a  href="https://developer.paypal.com/" target="_blank">PayPal developer account.</a>', 'pal-for-edd'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'paypal_for_edd_api_ex_sandbox_username',
                'name' => __('API Username ', 'pal-for-edd'),
                'desc' => __('Create sandbox accounts and obtain API credentials from within your <a href="https://developer.paypal.com/" target="_blank">PayPal developer account.</a>', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_ex_sandbox_password',
                'name' => __('API Password ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_ex_sandbox_signature',
                'name' => __('API Signature ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_ex_live_username',
                'name' => __('API Username ', 'pal-for-edd'),
                'desc' => __('Get your live account API credentials from your PayPal account profile under the API Access section or by using <a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_login-api-run" target="_blank">this tool.</a>', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_api_ex_live_password',
                'name' => __('API Password ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_api_ex_live_signature',
                'name' => __('API Signature ', 'pal-for-edd'),
                'type' => 'password'
            ),
            array(
                'id' => 'paypal_for_edd_ex_show_button_checkout_page',
                'name' => __('Checkout Page Display', 'pal-for-edd'),
                'desc' => __('Displaying the checkout button at the top of the checkout page will allow users to skip filling out the forms and can potentially increase conversion rates.', 'pal-for-edd'),
                'type' => 'checkbox',
            ),
            array(
                'id' => 'paypal_for_edd_ex_invoice_id_prefix',
                'name' => __('Invoice ID Prefix', 'pal-for-edd'),
                'desc' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_ex_skip_text',
                'name' => __('Express Checkout Message', 'pal-for-edd'),
                'desc' => __('This message will be displayed next to the PayPal Express Checkout button at the checkout page.', 'pal-for-edd'),
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_ex_skip_final_review',
                'name' => __('Skip Final Review', 'pal-for-edd'),
                'desc' => __('By default, users will be returned from PayPal and presented with a final review page which includes shipping and tax in the order details.  Enable this option to eliminate this page in the checkout process.', 'pal-for-edd'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'paypal_for_edd_ex_landingpage',
                'name' => __('Landing Page', 'pal-for-edd'),
                'type' => 'gateway_select',
                'desc' => __('Type of PayPal page to display as default. PayPal Account Optional must be checked for this option to be used.', 'pal-for-edd'),
                'options' => edd_get_payment_landing_page()
            ),
            array(
                'id' => 'paypal_for_edd_ex_payment_action',
                'name' => __('Payment Action ', 'pal-for-edd'),
                'type' => 'gateway_select',
                'desc' => __('Sale will capture the funds immediately when the order is placed. Authorization will authorize the payment but will not capture the funds. You would need to capture funds through your PayPal account when you are ready to deliver.', 'pal-for-edd'),
                'options' => edd_get_payment_action(),
            ),
            array(
                'id' => 'paypal_for_edd_ex_enabal_button',
                'name' => __('Custom Button URL', 'pal-for-edd'),
                'desc' => __('Custom Button ( If you select this option then please enter URL in Custom Button textbox, Otherwise payment button will not display. )', 'pal-for-edd'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'paypal_for_edd_ex_button_link',
                'type' => 'text'
            ),
            array(
                'id' => 'paypal_for_edd_ex_debug',
                'name' => __('Debug Log', 'pal-for-edd'),
                'desc' => sprintf(__('Enable logging<code>%s</code>', 'pal-for-edd'), paypal_for_edd_log_file_path('paypal_for_edd_express_checkout')),
                'type' => 'checkbox'
            )
        ),
    );
    return $paypal_for_edd_express_checkout_settings;
}

function edd_get_payment_landing_page() {

    $action = array(
        'login' => array(
            'admin_label' => __('Login', 'pal-for-edd')
        ),
        'billing' => array(
            'admin_label' => __('Billing', 'pal-for-edd')
        )
    );

    return apply_filters('edd_payment_action', $action);
}

function edd_get_payment_action() {

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

function paypal_for_edd_process_payment($purchase_data) {

    global $edd_options;
    $Paypal_For_EDD_Express_Checkout_Helper = new Paypal_For_EDD_Express_Checkout_Helper();
    $result_array = $Paypal_For_EDD_Express_Checkout_Helper->paypal_express_checkout_methods($purchase_data);
}

function show_button_checkout() {

    global $edd_options;
    $express_checkout_enable_payment_gatways = isset($edd_options['gateways']['paypal_for_edd']) ? true : false;
    $show_button_checkout_page = isset($edd_options['paypal_for_edd_ex_show_button_checkout_page']) ? true : false;

    $total_cart_amount = edd_get_cart_total( $discounts = false );
    
    if (empty($total_cart_amount) || $total_cart_amount == 0) {
        return;
    }

    if ($express_checkout_enable_payment_gatways == false) {
        return;
    }
    if ($show_button_checkout_page == false) {
        return;
    }

    $paypal_for_edd_ex_skip_text = isset($edd_options['paypal_for_edd_ex_skip_text']) ? $edd_options['paypal_for_edd_ex_skip_text'] : '';
    $enable_custom_paypal_buttons = isset($edd_options['paypal_for_edd_ex_enabal_button']) ? true : false;
    $buttons_url = EDD_EX_PLUGIN_PLUGIN_URL . "/public/images/paypal.png";

    if ($enable_custom_paypal_buttons) {
        $buttons_url = $edd_options['paypal_for_edd_ex_button_link'] ? $edd_options['paypal_for_edd_ex_button_link'] : $buttons_url;
    }
    ?>
    <fieldset id="edd_show_buttons" style="text-align: center;color: #1982d1">
        <a id="edd_express_checkout_button" href="javascript:void(0)">
            <img src="<?php echo $buttons_url; ?>">        
        </a>      			
        <?php echo '<p>' . $paypal_for_edd_ex_skip_text . '</p>'; ?>
    </fieldset>
    <?php
}

function show_full_shipping_details() {
    global $edd_options;
    
    $full_shipping_details = "";
    if (!isset($_SESSION['full_shipping_details'])) {
        return;
    }
    $full_shipping_details = $_SESSION['full_shipping_details'];
    $full_name = $full_shipping_details['FIRSTNAME'] . ' ' . $full_shipping_details['LASTNAME'];
    $address1 = $full_shipping_details['SHIPTONAME'];
    $address2 = $full_shipping_details['SHIPTOCITY'] . ', ' . $full_shipping_details['SHIPTOSTATE'] . ' ' . $full_shipping_details['SHIPTOZIP'];
    $country_name = $full_shipping_details['SHIPTOCOUNTRYNAME'] . ' (' . $full_shipping_details['SHIPTOCOUNTRYCODE'] . ')';
    $formaction = add_query_arg(array('payment-confirmation' => 'PayPalPlaceOrder'), get_permalink($edd_options['purchase_page']));
    $cancle_url = get_permalink($edd_options['purchase_page']);
    ?>
<script type="text/javascript">
    jQuery('header h1.entry-title').html();
    jQuery('header h1.entry-title').html('Review Order');
</script>
    <fieldset id="edd_show_full_shipping_details" style="">
        <input id="form_action_url" type="hidden" value="<?php echo isset($formaction)?$formaction:""; ?>">
        <span><legend><?php echo __('Shipping Address', 'pal-for-edd'); ?></legend></span>
        <p><label ><?php echo isset($full_name)?$full_name:""; ?></label></p>
        <p><label ><?php echo isset($address1)?$address1:""; ?></label></p>
        <p><label ><?php echo isset($address2)?$address2:""; ?></label></p>
        <p><label ><?php echo isset($country_name)?$country_name:""; ?></label></p>
        <p>                
            <a class="button paypal_for_edd_express_cancel" href="<?php echo isset($cancle_url)?$cancle_url:""; ?>"><?php echo __('Cancel Order','pal-for-edd'); ?></a>
            <span class="paypal_for_edd_express_submit_span">
                <input type="submit" class="paypal_for_edd_express_submit" value="Place Order">
            </span>
        </p>            
    </fieldset>

<?php
}

if(!function_exists('paypal_for_edd_log_file_path')) {
    function paypal_for_edd_log_file_path($handle) {
        return trailingslashit(PAYPAL_FOR_EDD_WORDPRESS_LOG_DIR) . $handle . '_' . sanitize_file_name(wp_hash($handle)) . '.log';
    }
}