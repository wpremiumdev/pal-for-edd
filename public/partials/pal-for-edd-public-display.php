<?php

function paypal_for_edd_process_to_payment() {


    do_action('edd_pre_process_purchase');
    // Make sure the cart isn't empty
    if (!edd_get_cart_contents() && !edd_cart_has_fees()) {
        $valid_data = false;
        edd_set_error('empty_cart', __('Your cart is empty', 'easy-digital-downloads'));
    } else {
        // Validate the form $_POST data
        $valid_data = edd_purchase_form_validate_fields();

        if ($valid_data['gateway'] != 'paypal_for_edd') {
            $valid_data['gateway'] = 'paypal_for_edd';
        }
        // Allow themes and plugins to hook to errors
        do_action('edd_checkout_error_checks', $valid_data, $_POST);
    }

    $is_ajax = isset($_POST['edd_ajax']);

    // Process the login form
    if (isset($_POST['edd_login_submit'])) {
        edd_process_purchase_login();
    }

    // Validate the user
    $user = edd_get_purchase_form_user($valid_data);
    if (false === $valid_data || !$user) {
        if ($is_ajax) {
            do_action('edd_ajax_checkout_errors');
            edd_die();
        } else {
            return false;
        }
    }

    if ($is_ajax) {
        echo 'success';
        edd_die();
    }

    // Setup user information
    $user_info = array(
        'id' => isset($valid_data['logged_in_user']['user_id'])?$valid_data['logged_in_user']['user_id']:'',
        'email' => isset($valid_data['logged_in_user']['user_email'])?$valid_data['logged_in_user']['user_email']:'',
        'first_name' => isset($valid_data['logged_in_user']['user_first'])?$valid_data['logged_in_user']['user_first']:'',
        'last_name' => isset($valid_data['logged_in_user']['user_last'])?$valid_data['logged_in_user']['user_last']:'',
        'discount' => isset($valid_data['discount'])?$valid_data['discount']:'',
        'address' => '',
    );

    $auth_key = defined('AUTH_KEY') ? AUTH_KEY : '';

    // Setup purchase information
    $purchase_data = array(
        'AJAX_DIRECT' => 'yes',
        'downloads' => edd_get_cart_contents(),
        'fees' => edd_get_cart_fees(), // Any arbitrary fees that have been added to the cart
        'subtotal' => edd_get_cart_subtotal(), // Amount before taxes and discounts
        'discount' => edd_get_cart_discounted_amount(), // Discounted amount
        'tax' => edd_get_cart_tax(), // Taxed amount
        'price' => edd_get_cart_total(), // Amount after taxes
        'purchase_key' => strtolower(md5($user['user_email'] . date('Y-m-d H:i:s') . $auth_key . uniqid('edd', true))), // Unique key
        'user_email' => $user['user_email'],
        'date' => date('Y-m-d H:i:s', current_time('timestamp')),
        'user_info' => stripslashes_deep($user_info),
        'post_data' => $_POST,
        'cart_details' => edd_get_cart_content_details(),
        'gateway' => $valid_data['gateway'],
        'card_info' => $valid_data['cc_info']
    );

    // Add the user data for hooks
    $valid_data['user'] = $user;

    // Allow themes and plugins to hook before the gateway
    do_action('edd_checkout_before_gateway', $_POST, $user_info, $valid_data);

    // If the total amount in the cart is 0, send to the manual gateway. This emulates a free download purchase
    if (!$purchase_data['price']) {
        // Revert to manual
        $purchase_data['gateway'] = 'manual';
        $_POST['edd-gateway'] = 'manual';
    }

    // Allow the purchase data to be modified before it is sent to the gateway
    $purchase_data = apply_filters(
            'edd_purchase_data_before_gateway', $purchase_data, $valid_data
    );

    // Setup the data we're storing in the purchase session
    $session_data = $purchase_data;

    // Make sure credit card numbers are never stored in sessions
    unset($session_data['card_info']['card_number']);

    // Used for showing download links to non logged-in users after purchase, and for other plugins needing purchase data.
    edd_set_purchase_session($session_data);

    // Send info to the gateway for payment processing
    edd_send_to_gateway($purchase_data['gateway'], $purchase_data);
    edd_die();
}

add_action('wp_ajax_paypal_for_edd_process_to_payment', 'paypal_for_edd_process_to_payment');
add_action('wp_ajax_nopriv_paypal_for_edd_process_to_payment', 'paypal_for_edd_process_to_payment');

function paypal_for_edd_cancel_payment() {
    edd_empty_cart();
    unset($_SESSION['cart_item_array']);
    unset($_SESSION['full_shipping_details']);
    echo "Success";
    die();
}

add_action('wp_ajax_paypal_for_edd_cancel_payment', 'paypal_for_edd_cancel_payment');
add_action('wp_ajax_nopriv_paypal_for_edd_cancel_payment', 'paypal_for_edd_cancel_payment');


function pay_for_edd_get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
    if ($args && is_array($args)) {
        extract($args);
    }
    $located = pay_for_edd_psc_locate_template($template_name, $template_path, $default_path);
    if (!file_exists($located)) {
        _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.1');
        return;
    }
    // Allow 3rd party plugin filter template file from their plugin
    $located = apply_filters('pay_for_edd_get_template', $located, $template_name, $args, $template_path, $default_path);
    do_action('pay_for_edd_before_template_part', $template_name, $template_path, $located, $args);
    include( $located );
    do_action('pay_for_edd_after_template_part', $template_name, $template_path, $located, $args);
}

function template_path() {
    return apply_filters('pay_for_edd_template_path', 'pal-for-edd/');
}

function pay_for_edd_psc_locate_template($template_name, $template_path = '', $default_path = '') {
    if (!$template_path) {
        $template_path = template_path();
    }
    if (!$default_path) {
        $default_path = PAYPAL_FOR_EDD_PLUGIN_DIR_PATH . '/template/';
    }
    // Look within passed path within the theme - this is priority
    $template = locate_template(
            array(
                trailingslashit($template_path) . $template_name,
                $template_name
            )
    );
    // Get default template
    if (!$template) {
        $template = $default_path . $template_name;
    }
    // Return what we found
    return apply_filters('pay_for_edd_locate_template', $template, $template_name, $template_path);
}

function is_receipt_page($posted){
    $result = false;    
    if( isset($posted['edd-order-pay']) && !empty($posted['edd-order-pay']) ){
        $result = true;
    }
    return $result;
    
}
