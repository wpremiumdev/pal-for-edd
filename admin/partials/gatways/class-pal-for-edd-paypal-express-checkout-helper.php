<?php

ob_start();

class Paypal_For_EDD_Express_Checkout_Helper {

    public $PaymentOrderItems = array();

    public function __construct() {

        global $edd_options;

        $this->id = 'paypal_for_edd';
        $this->edd_options = $edd_options;
        $this->testmode = $test_mode = ( $test_mode = ( edd_is_test_mode() ) ? TRUE : ( isset($this->edd_options['paypal_for_edd_ex_testmode']) ? TRUE : FALSE ) );
        $this->PAYPAL_URL = "";
        $this->payment_action = isset($this->edd_options['paypal_for_edd_ex_payment_action']) ? $this->edd_options['paypal_for_edd_ex_payment_action'] : 'Sale';
        $this->landing_page = isset($this->edd_options['paypal_for_edd_ex_landingpage']) ? $this->edd_options['paypal_for_edd_ex_landingpage'] : 'Login';
        $this->debug = isset($this->edd_options['paypal_for_edd_ex_debug']) ? TRUE : FALSE;
        $this->skip_final_review = isset($this->edd_options['paypal_for_edd_ex_skip_final_review']) ? TRUE : FALSE;

        $this->checkout_page = isset($this->edd_options['purchase_page']) ? $this->edd_options['purchase_page'] : '';
        $this->success_transaction_page = isset($this->edd_options['success_page']) ? $this->edd_options['success_page'] : '';
        $this->fail_transaction_page = isset($this->edd_options['failure_page']) ? $this->edd_options['failure_page'] : '';
        $this->invoice_prifix = isset($this->edd_options['paypal_for_edd_ex_invoice_id_prefix']) ? $this->edd_options['paypal_for_edd_ex_invoice_id_prefix'] : '';
        $this->paypal_for_edd_notifyurl = site_url('?Paypal_For_Edd&action=ipn_handler');

        $this->listener_url = '';
        $this->returnURL = '';
        $this->cancelURL = '';
        $this->payment_AMT = 0;
        $this->payment_TAX = 0;
        $this->calculated_TOTAL = 0;
        $this->payment_Discount = 0;
        $this->payment_FEES = array();
        $this->AJAX_DIRECT = false;
        $this->payment_ids = "";

        if ($this->testmode) {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
            $this->api_username = isset($this->edd_options['paypal_for_edd_api_ex_sandbox_username']) ? trim($this->edd_options['paypal_for_edd_api_ex_sandbox_username']) : '';
            $this->api_password = isset($this->edd_options['paypal_for_edd_api_ex_sandbox_password']) ? trim($this->edd_options['paypal_for_edd_api_ex_sandbox_password']) : '';
            $this->api_signature = isset($this->edd_options['paypal_for_edd_api_ex_sandbox_signature']) ? trim($this->edd_options['paypal_for_edd_api_ex_sandbox_signature']) : '';
        } else {
            $this->api_username = isset($this->edd_options['paypal_for_edd_api_ex_live_username']) ? trim($this->edd_options['paypal_for_edd_api_ex_live_username']) : '';
            $this->api_password = isset($this->edd_options['paypal_for_edd_api_ex_live_password']) ? trim($this->edd_options['paypal_for_edd_api_ex_live_password']) : '';
            $this->api_signature = isset($this->edd_options['paypal_for_edd_api_ex_live_signature']) ? trim($this->edd_options['paypal_for_edd_api_ex_live_signature']) : '';
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }
    }

    public function paypal_express_checkout_methods($posted) {

        if (is_array($posted) && count($posted) > 0) {

            edd_clear_errors();
            $this->is_ajax_call_direct_payment($posted);
            $_SESSION['cart_item_array'] = $posted;
            $this->listener_url = add_query_arg('edd-listener', 'IPN', home_url('index.php'));
            $this->returnURL = add_query_arg(array('payment-confirmation' => 'PayPalSetExpressCheckout'), get_permalink($this->success_transaction_page));
            $this->cancelURL = add_query_arg(array('payment-confirmation' => 'PayPalExpressCheckoutCancle'), get_permalink($this->fail_transaction_page));

            $resArray = $this->CallSetExpressCheckout($this->returnURL, $this->cancelURL, 'false', $posted);

            if (isset($resArray["ACK"]) && ($resArray["ACK"] == "Success" || $resArray["ACK"] == "SUCCESSWITHWARNING")) {

                $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'SetExpressCheckout', $resArray['REQUESTDATA']);

                if ($this->AJAX_DIRECT) {
                    echo $resArray['REDIRECTURL'];
                    die();
                } else {
                    wp_redirect($resArray['REDIRECTURL']);
                    exit;
                }
            } else if (isset($resArray["ACK"]) && $resArray["ACK"] == "Failure") {
                $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'SetExpressCheckout', $resArray);
                if (isset($resArray['ERRORS']) && count($resArray['ERRORS']) > 0) {
                    foreach ($resArray['ERRORS'] as $key => $value) {
                        edd_set_error('payment_express_checkout_error', __($value['L_LONGMESSAGE'], 'pal-for-edd'));
                        if ($this->AJAX_DIRECT) {
                            echo get_permalink($this->checkout_page);
                            die();
                        } else {
                            wp_redirect(get_permalink($this->checkout_page));
                            exit;
                        }
                    }
                }
            } else if (isset($resArray["CURL_ERROR"]) && strlen($resArray["CURL_ERROR"]) > 0) {
                $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'SetExpressCheckout', $resArray['REQUESTDATA']);
                edd_set_error('payment_express_checkout_error', __($resArray["CURL_ERROR"], 'pal-for-edd'));
                if ($this->AJAX_DIRECT) {
                    echo get_permalink($this->checkout_page);
                    die();
                } else {
                    wp_redirect(get_permalink($this->checkout_page));
                    exit;
                }
            }
        }
    }

    public function paypal_express_checkout_GetShippingDetails($posted) {


        if (isset($_GET['token']) && isset($_GET['PayerID'])) {

            $token = sanitize_text_field($_GET['token']);
            $payerID = sanitize_text_field($_GET['PayerID']);
            $result = $this->CallGetShippingDetails($_GET['token']);

            if (isset($result['ACK']) && $result['ACK'] == 'Success') {

                $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'GetExpressCheckout', $result['REQUESTDATA']);
                $this->update_or_add_customer_data($result);

                if ($this->skip_final_review) {
                    $this->Do_Payment_Confirm($result, $token, $payerID);
                } else {
                    $_SESSION['full_shipping_details'] = $result;
                    wp_redirect(get_permalink($this->checkout_page));
                }
            } else {
                $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'GetExpressCheckout', $result['REQUESTDATA']);
                edd_set_error('payment_express_checkout_error', __($result['L_LONGMESSAGE'], 'pal-for-edd'));
                wp_redirect(get_permalink($this->checkout_page));
                exit;
            }
        } else {

            edd_set_error('payment_express_checkout_error', __('PayPal Express Checkout Empty Token & Payerid Try Again!', 'pal-for-edd'));
            wp_redirect(get_permalink($this->checkout_page));
            exit;
        }
    }

    public function update_or_add_customer_data($result) {

        $posted = $_SESSION['cart_item_array'];

        if (!wp_verify_nonce($posted['gateway_nonce'], 'edd-gateway')) {
            wp_die(__('Nonce verification has failed', 'pal-for-edd'), __('Error', 'pal-for-edd'), array('response' => 403));
        }

        if (isset($posted['AJAX_DIRECT']) && $posted['AJAX_DIRECT'] == 'yes') {
            $posted['user_email'] = $result['EMAIL'];
            $posted['user_info'] = array(
                'id' => '0',
                'email' => $result['EMAIL'],
                'first_name' => $result['FIRSTNAME'],
                'last_name' => $result['LASTNAME'],
                'discount' => $result['SHIPDISCAMT'],
                'address' => array(
                    'line1' => $result['SHIPTOSTREET'],
                    'line2' => '',
                    'city' => $result['SHIPTOCITY'],
                    'state' => $result['SHIPTOSTATE'],
                    'country' => $result['SHIPTOCOUNTRYCODE'],
                    'zip' => $result['SHIPTOZIP']
                )
            );
        }
        $payment = edd_insert_payment($posted);
        $this->check_email_exist_or_not($result['EMAIL']);
        $posted['payment_id'] = $payment;
        $posted['payment_ids'] = $this->payment_ids;
        $_SESSION['cart_item_array'] = $posted;
        return;
    }

    public function check_email_exist_or_not($email) {

        $customer = new EDD_Customer($email);

        if (isset($customer->id) && !empty($customer->id) && strlen($customer->id) > 0) {
            $this->payment_ids = $customer->id;
        }

        return $this->payment_ids;
    }

    public function update_user_info_post_mata_by_id($posted_session, $result) {

        $result_of_mata_array = get_post_meta($posted_session['payment_id'], '_edd_payment_meta');
        $result_of_mata = $result_of_mata_array[0];
        $result_of_mata['cart_details'] = $posted_session['cart_details'];
        $result_of_mata['email'] = $result['EMAIL'];
        $result_of_mata['user_info'] = array(
            'id' => $result_of_mata['user_info']['id'],
            'email' => $result['EMAIL'],
            'first_name' => $result['FIRSTNAME'],
            'last_name' => $result['LASTNAME'],
            'discount' => $result['SHIPDISCAMT'],
            'address' => array(
                'line1' => $result['SHIPTOSTREET'],
                'line2' => '',
                'city' => $result['SHIPTOCITY'],
                'state' => $result['SHIPTOSTATE'],
                'country' => $result['SHIPTOCOUNTRYCODE'],
                'zip' => $result['SHIPTOZIP']
            )
        );

        update_post_meta($posted_session['payment_id'], '_edd_payment_user_email', $result['EMAIL']);
        update_post_meta($posted_session['payment_id'], '_edd_payment_meta', $result_of_mata);

        return;
    }

    public function update_edd_customers($id, $result) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'edd_customers';

        $customer_email = isset($result['EMAIL']) ? $result['EMAIL'] : 'example@gmail.com';
        $customer_name = isset($result['FIRSTNAME']) ? $result['FIRSTNAME'] : 'Unname Customer';
        $customer_name .= isset($result['LASTNAME']) ? ' ' . $result['LASTNAME'] : '';

        $wpdb->query($wpdb->prepare(" UPDATE {$table_name} SET  email = %s, name = %s WHERE id IN (" . $id . ")", $customer_email, $customer_name));

        return;
    }

    public function paypalconfig_array() {
        return array(
            'Sandbox' => $this->testmode == 'yes' ? TRUE : FALSE,
            'APIUsername' => $this->api_username,
            'APIPassword' => $this->api_password,
            'APISignature' => $this->api_signature
        );
    }

    public function is_ajax_call_direct_payment($posted) {

        if (isset($posted['AJAX_DIRECT']) && $posted['AJAX_DIRECT'] == 'yes') {
            $this->AJAX_DIRECT = true;
        } else {
            $customer = new EDD_Customer($posted['user_email']);
            $this->payment_ids = $customer->id;
        }
        return;
    }

    public function CallSetExpressCheckout($returnURL, $cancelURL, $usePayPalCredit = false, $posted) {

        $PayPalConfig = $this->paypalconfig_array();
        $PayPal = new PayPal_Express_PayPal($PayPalConfig);

        $customer_email = "";
        if (isset($posted['post_data']['edd_email'])) {
            $customer_email = sanitize_email($posted['post_data']['edd_email']);
        } elseif (is_user_logged_in()) {
            global $current_user;
            $customer_email = $current_user->user_email;
        }

        $SECFields = array(
            'token' => '', // A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
            'maxamt' => '', // The expected maximum total amount the order will be, including S&H and sales tax.
            'returnurl' => urldecode($returnURL), // Required.  URL to which the customer will be returned after returning from PayPal.  2048 char max.
            'cancelurl' => urldecode($cancelURL), // Required.  URL to which the customer will be returned if they cancel payment on PayPal's site.            
            'allownote' => 1, // The value 1 indiciates that the customer may enter a note to the merchant on the PayPal page during checkout.  The note is returned in the GetExpresscheckoutDetails response and the DoExpressCheckoutPayment response.  Must be 1 or 0.
            'addroverride' => 1, // The value 1 indiciates that the PayPal pages should display the shipping address set by you in the SetExpressCheckout request, not the shipping address on file with PayPal.  This does not allow the customer to edit the address here.  Must be 1 or 0.
            'localecode' => get_locale(), // Locale of pages displayed by PayPal during checkout.  Should be a 2 character country code.  You can retrive the country code by passing the country name into the class' GetCountryCode() function.
            'surveyquestion' => '',
            'skipdetails' => 1, // This is a custom field not included in the PayPal documentation.  It's used to specify whether you want to skip the GetExpressCheckoutDetails part of checkout or not.  See PayPal docs for more info.
            'email' => $customer_email // Email address of the buyer as entered during checkout.  PayPal uses this value to pre-fill the PayPal sign-in page.  127 char max.            
        );
        $SECFields['solutiontype'] = 'Sole';
        $SECFields['landingpage'] = $this->landing_page;
        $Payments = array();

        if (isset($posted['price'])) {
            $this->payment_AMT = number_format(( $posted['price']), 2, '.', '');
        }

        if (isset($posted['discount'])) {
            $this->payment_Discount = number_format(( $posted['discount']), 2, '.', '');
        }

        if (isset($posted['tax'])) {
            $this->payment_TAX = number_format(( $posted['tax']), 2, '.', '');
        }

        if (is_array($posted['fees']) && count($posted['fees']) > 0) {
            $this->payment_FEES = $posted['fees'];
        }

        // Add Line Item
        if (sizeof($posted['cart_details']) != 0) {
            $this->add_product_line_item($posted['cart_details']);
        }
        // Add Discount Line
        if (isset($this->payment_Discount) && $this->payment_Discount > 0) {
            $this->add_discount_line($this->payment_Discount);
        }
        // Add Fees  Line
        if (is_array($this->payment_FEES) && count($this->payment_FEES) > 0) {
            $this->add_fees_line($this->payment_FEES);
        }

        $this->calculated_TOTAL += $this->payment_TAX;

        // Adjustment  Line
        if ($this->payment_AMT != number_format(( $this->calculated_TOTAL), 2, '.', '')) {
            $this->add_adjustment_line();
        }

        $item_amount = $this->payment_AMT - $this->payment_TAX;
        $Payment = array(
            'amt' => $this->payment_AMT,
            'currencycode' => edd_get_currency(),
            'shippingamt' => 0,
            'itemamt' => $item_amount,
            'taxamt' => $this->payment_TAX,
            'shippingdiscamt' => $this->payment_Discount,
            'paymentaction' => $this->payment_action,
            'notifyurl' => $this->paypal_for_edd_notifyurl,
        );

        $Payment['order_items'] = $this->PaymentOrderItems;
        array_push($Payments, $Payment);

        $PayPalRequestData = array(
            'SECFields' => $SECFields,
            'Payments' => $Payments
        );

        $PayPalResult = $PayPal->SetExpressCheckout($PayPalRequestData);
        return $PayPalResult;
    }

    public function CallGetShippingDetails($token) {

        $PayPalConfig = $this->paypalconfig_array();
        $PayPal = new PayPal_Express_PayPal($PayPalConfig);
        $PayPalResult = $PayPal->GetExpressCheckoutDetails($token);
        return $PayPalResult;
    }

    public function Do_Payment_Confirm($result, $token, $payerID) {

        if (is_array($result) && count($result) > 0) {
            $result = $result;
        } else {
            $result = $_SESSION['full_shipping_details'];
        }

        if (!isset($token) || empty($token)) {
            $token = $_SESSION['full_shipping_details']['TOKEN'];
        }

        if (!isset($payerID) || empty($payerID)) {
            $payerID = $_SESSION['full_shipping_details']['PAYERID'];
        }
        $posted_session_data = $_SESSION['cart_item_array'];
        unset($_SESSION['full_shipping_details']);
        $chosen_shipping_methods = 'Express Checkout';
        $shiptoname = explode(' ', $result['SHIPTONAME']);
        $firstname = $shiptoname[0];
        $lastname = $shiptoname[1];

        $shipping_first_name = $firstname;
        $shipping_last_name = $lastname;
        $full_name = $shipping_first_name . ' ' . $shipping_last_name;
        if (is_user_logged_in()) {
            $userLogined = wp_get_current_user();
        } else {
            
        }
        $result = $this->ConfirmPayment($result, $token, $payerID);
        if (isset($result['ACK']) && ($result['ACK'] == 'Success' || $result['ACK'] == 'SuccessWithWarning')) {
            $this->update_payment_success_data($result['PAYMENTINFO_0_PAYMENTSTATUS'], $posted_session_data['payment_id'], $posted_session_data['payment_ids'], $result);
            $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'ConfirmPayment', $result['REQUESTDATA']);
            wp_redirect(get_permalink($this->success_transaction_page));
            exit();
        } else {
            $this->update_payment_success_data('failed', $posted_session_data['payment_id'], $posted_session_data['payment_ids'], $result);
            $this->paypal_for_edd_write_log('paypal_for_edd_express_checkout', 'ConfirmPayment', $result['REQUESTDATA']);
            wp_redirect(get_permalink($this->fail_transaction_page));
            exit();
        }
    }

    public function ConfirmPayment($result, $token, $payerID) {

        $posted = $_SESSION['cart_item_array'];
        unset($_SESSION['cart_item_array']);
        unset($_SESSION['full_shipping_details']);

        $PayPalConfig = $this->paypalconfig_array();
        $shipping_first_name = isset($result['FIRSTNAME']) ? $result['FIRSTNAME'] : '';
        $shipping_last_name = isset($result['LASTNAME']) ? $result['LASTNAME'] : '';
        $shipping_address_1 = isset($result['SHIPTOSTREET']) ? $result['SHIPTOSTREET'] : '';
        $shipping_address_2 = isset($result['SHIPTOSTREET2']) ? $result['SHIPTOSTREET2'] : '';
        $shipping_city = isset($result['SHIPTOCITY']) ? $result['SHIPTOCITY'] : '';
        $shipping_state = isset($result['SHIPTOSTATE']) ? $result['SHIPTOSTATE'] : '';
        $shipping_postcode = isset($result['SHIPTOZIP']) ? $result['SHIPTOZIP'] : '';
        $shipping_country = isset($result['SHIPTOCOUNTRYCODE']) ? $result['SHIPTOCOUNTRYCODE'] : '';

        $DECPFields = array(
            'token' => urlencode($token),
            'payerid' => urlencode($payerID)
        );
        $Payments = array();

        if (isset($posted['price'])) {
            $this->payment_AMT = number_format(( $posted['price']), 2, '.', '');
        }

        if (isset($posted['discount'])) {
            $this->payment_Discount = number_format(( $posted['discount']), 2, '.', '');
        }

        if (isset($posted['tax'])) {
            $this->payment_TAX = number_format(( $posted['tax']), 2, '.', '');
        }

        if (is_array($posted['fees']) && count($posted['fees']) > 0) {
            $this->payment_FEES = $posted['fees'];
        }

        // Add Line Item
        if (sizeof($posted['cart_details']) != 0) {
            $this->add_product_line_item($posted['cart_details']);
        }
        // Add Discount Line
        if (isset($this->payment_Discount) && $this->payment_Discount > 0) {
            $this->add_discount_line($this->payment_Discount);
        }
        // Add Fees  Line
        if (is_array($this->payment_FEES) && count($this->payment_FEES) > 0) {
            $this->add_fees_line($posted['fees']);
        }

        $this->calculated_TOTAL += $this->payment_TAX;

        // Adjustment  Line
        if ($this->payment_AMT != number_format(( $this->calculated_TOTAL), 2, '.', '')) {
            $this->add_adjustment_line();
        }

        $item_amount = $this->payment_AMT - $this->payment_TAX;

        $Payment = array(
            'amt' => $result['AMT'],
            'currencycode' => edd_get_currency(),
            'shippingamt' => '',
            'itemamt' => $result['ITEMAMT'],
            'taxamt' => $result['TAXAMT'],
            'desc' => '',
            'custom' => '',
            'invnum' => $this->invoice_prifix . '' . substr(microtime(), -5),
            'notifyurl' => '',
            'shiptoname' => $shipping_first_name . ' ' . $shipping_last_name,
            'shiptostreet' => $shipping_address_1,
            'shiptostreet2' => $shipping_address_2,
            'shiptocity' => $shipping_city,
            'shiptostate' => $shipping_state,
            'shiptozip' => $shipping_postcode,
            'shiptocountrycode' => $shipping_country,
            'shiptophonenum' => '',
            'notetext' => '',
            'allowedpaymentmethod' => '',
            'paymentaction' => $this->payment_action,
            'paymentrequestid' => '',
            'sellerpaypalaccountid' => '',
            'sellerid' => '',
            'sellerusername' => '',
            'sellerregistrationdate' => '',
            'softdescriptor' => ''
        );

        $PaymentOrderItems = array();
        $Payment['order_items'] = $this->PaymentOrderItems;
        array_push($Payments, $Payment);

        $PayPalRequestData = array(
            'DECPFields' => $DECPFields,
            'Payments' => $Payments,
        );

        $PayPal = new PayPal_Express_PayPal($PayPalConfig);
        $PayPalResult = $PayPal->DoExpressCheckoutPayment($PayPalRequestData);

        if ($PayPal->APICallSuccessful($PayPalResult['ACK'])) {
            edd_empty_cart();
        } else {

            wp_redirect(get_permalink($this->fail_transaction_page));
            edd_set_error('payment_express_checkout_error', __($PayPalResult['L_LONGMESSAGE0'], 'pal-for-edd'));
            exit;
        }
        return $PayPalResult;
    }

    public function add_product_line_item($cart_details) {

        foreach ($cart_details as $item) {

            $item_price = round(( $item['subtotal'] / $item['quantity']), 2);
            if ($item_price <= 0) {
                $item_price = 0;
            }
            $Item = array(
                'name' => $item['name'], // Item name. 127 char max.
                'desc' => '', // Item description. 127 char max.
                'amt' => number_format(( $item_price), 2, '.', ''), // Cost of item.
                'number' => $item['id'], // Item number.  127 char max.
                'qty' => $item['quantity'], // Item qty on order.  Any positive integer.
                'itemurl' => ''
            );
            array_push($this->PaymentOrderItems, $Item);

            $this->calculated_TOTAL += number_format(( $item_price * $item['quantity']), 2, '.', '');
        }

        return;
    }

    public function get_edd_customers_list($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'edd_customers';
        $qry = "SELECT * FROM " . $table_name . " where payment_ids IN (" . $id . ")";
        $payment_ids = $wpdb->get_results($qry);
        if (isset($payment_ids[0]->id) && !empty($payment_ids[0]->id)) {
            $this->payment_ids = $payment_ids[0]->id;
            $this->update_edd_customers($this->payment_ids, '');
        }
        return;
    }

    public function add_discount_line($discount) {
        $Item = array(
            'name' => __('Total Discount', 'pal-for-edd'),
            'amt' => '-' . number_format(( $discount), 2, '.', ''),
            'qty' => 1
        );

        array_push($this->PaymentOrderItems, $Item);
        $this->calculated_TOTAL -= $discount;
        return;
    }

    public function add_fees_line($fees) {

        foreach ($fees as $value) {
            $Item = array(
                'NAME' => __($value['label'], 'express-checkout'),
                'AMT' => number_format(( $value['amount']), 2, '.', ''),
                'QTY' => 1,
            );
        }
        array_push($this->PaymentOrderItems, $Item);
        $this->calculated_TOTAL += number_format(( $value['amount']), 2, '.', '');
        return;
    }

    public function add_adjustment_line() {


        $Item = array(
            'NAME' => __('PayPal Rounding Adjustment', 'pal-for-edd'),
            'AMT' => - (number_format(( $this->calculated_TOTAL - $this->payment_AMT), 2, '.', '')),
            'QTY' => 1,
        );

        array_push($this->PaymentOrderItems, $Item);
        return;
    }

    public function paypal_for_edd_write_log($handle, $response_name, $result_array) {

        if ($this->debug == false) {
            return;
        }

        $log = new PayPal_For_EDD_Logger();
        unset($result_array['RAWREQUEST']);
        unset($result_array['RAWRESPONSE']);
        $message = $this->pattern_to_star($result_array);
        $log->add($handle, $response_name . '->' . print_r($message, true));
        return;
    }

    public function pattern_to_star($result) {

        foreach ($result as $key => $value) {
            if ("USER" == $key || "PWD" == $key || "BUTTONSOURCE" == $key || "SIGNATURE" == $key || "EMAIL" == $key || "PAYERID" == $key || "PAYERID" == $key || "TOKEN" == $key) {

                $str_length = strlen($value);
                $ponter_data = "";
                for ($i = 0; $i <= $str_length; $i++) {
                    $ponter_data .= '*';
                }
                $result[$key] = $ponter_data;
            }
        }

        return $result;
    }

    public function update_payment_success_data($status, $id, $ids, $result) {

        if ('failed' == $status) {
            $this->update_post_table($status, $id, '2', $result['REQUESTDATA']);
        } else {
            //edd_update_payment_status($payment, $is_transaction_info['PAYMENTSTATUS'] == 'Completed' ? 'publish' : 'pending');
            $this->update_edd_customers_table($ids, $result['PAYMENTS']);
            $this->update_post_table($status, $id, '1', $result['REQUESTDATA'], $result['PAYMENTINFO_0_TRANSACTIONID']);
            $this->update_post_mata_table($id);
        }
        return;
    }

    public function update_edd_customers_table($ids, $result) {


        $exist_data = $this->get_exist_purchase_value_count_edd_customer($ids);
        $purchase_value = 0;
        $purchase_count = 0;

        if (is_array($exist_data) && count($exist_data) > 0) {
            $purchase_value = $exist_data[0]->purchase_value;
            $purchase_count = $exist_data[0]->purchase_count;
        }
        $this->update_purchase_value_count_edd_customer($ids, $result, $purchase_value, $purchase_count);
        return;
    }

    public function get_exist_purchase_value_count_edd_customer($ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'edd_customers';
        $qry = "SELECT purchase_value, purchase_count FROM " . $table_name . " where id=" . $ids;
        $result = $wpdb->get_results($qry);
        if (is_array($result) && count($result) > 0) {
            return $result;
        } else {
            return 0;
        }
    }

    public function update_purchase_value_count_edd_customer($ids, $result, $purchase_value, $purchase_count) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'edd_customers';
        $payment_amt = isset($result[0]['AMT']) ? number_format(( $result[0]['AMT']), 2, '.', '') : 0;

        $final_amount = number_format(( $payment_amt + $purchase_value), 2, '.', '');
        $final_count = $purchase_count + 1;

        $wpdb->query($wpdb->prepare(" UPDATE {$table_name} SET  purchase_value = %f, purchase_count = %d WHERE id IN (" . $ids . ")", $final_amount, $final_count));
        return;
    }

    public function update_post_table($status, $id, $counter, $result, $transactionid) {

        $my_post = array();
        $my_post['ID'] = $id;
        $my_post['post_title'] = isset($transactionid) ? $transactionid : '';
        $my_post['post_status'] = $status == 'Completed' ? 'publish' : 'pending';
        $my_post['comment_count'] = $counter;
        wp_update_post($my_post);
        return;
    }

    public function update_post_mata_table($id) {

        update_post_meta($id, '_edd_completed_date', current_time('mysql'));
        return;
    }

}
ob_flush();