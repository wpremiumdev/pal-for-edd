<?php

class Paypal_For_EDD_PayPal_Advanced_Helper {

    public function __construct() {

        global $edd_options;

        $this->id = 'paypal_advanced';
        $this->edd_options = $edd_options;
        $this->api_version = '120';

        $this->title = isset($this->edd_options['Title']) ? $this->edd_options['Title'] : '';
        $this->description = isset($this->edd_options['Description']) ? $this->edd_options['Description'] : '';
        $this->testmode = $test_mode = ( $test_mode = ( edd_is_test_mode() ) ? TRUE : ( isset($this->edd_options['paypal_for_edd_advanced_testmode']) ? TRUE : FALSE ) );
        $this->debug = isset($this->edd_options['paypal_for_edd_advanced_debug']) ? TRUE : FALSE;
        $this->paymentaction = isset($this->edd_options['paypal_for_edd_api_advanced_action']) ? $this->edd_options['paypal_for_edd_api_advanced_action'] : 'Sale';
        $this->layout = "";
        $this->mobilemode = isset($this->edd_options['paypal_for_edd_advanced_mobile_mode']) ? $this->edd_options['paypal_for_edd_advanced_mobile_mode'] : 'yes';
        $this->invoice_prifix = isset($this->edd_options['paypal_for_edd_advanced_invoice_prefix']) ? $this->edd_options['paypal_for_edd_advanced_invoice_prefix'] : '';

        $this->purchase_page = isset($this->edd_options['purchase_page']) ? $this->edd_options['purchase_page'] : '';
        $this->success_page = isset($this->edd_options['success_page']) ? $this->edd_options['success_page'] : '';
        $this->failure_page = isset($this->edd_options['failure_page']) ? $this->edd_options['failure_page'] : '';

        $this->page_collapse_bgcolor = isset($this->edd_options['paypal_for_edd_advanced_page_collapse_bgcolor']) ? $this->edd_options['paypal_for_edd_advanced_page_collapse_bgcolor'] : '#1e73be';
        $this->page_collapse_textcolor = isset($this->edd_options['paypal_for_edd_advanced_page_collapse_textcolor']) ? $this->edd_options['paypal_for_edd_advanced_page_collapse_textcolor'] : '#81d742';
        $this->page_button_bgcolor = isset($this->edd_options['paypal_for_edd_advanced_page_button_bgcolor']) ? $this->edd_options['paypal_for_edd_advanced_page_button_bgcolor'] : '#dd9933';
        $this->page_button_textcolor = isset($this->edd_options['paypal_for_edd_advanced_page_button_textcolor']) ? $this->edd_options['paypal_for_edd_advanced_page_button_textcolor'] : '#dd3333';
        $this->label_textcolor = isset($this->edd_options['paypal_for_edd_advanced_label_textcolor']) ? $this->edd_options['paypal_for_edd_advanced_label_textcolor'] : '#8224e3';

        $this->securetoken = '';
        $this->secure_token_id = '';
        $this->Order_id = '';
        $this->order_key = '';
        $this->parsed_response = '';
        $this->is_mode = '';
        $this->home_url = is_ssl() ? home_url('/', 'https') : home_url('/');
        $this->returnURL = add_query_arg(array('edd-advanced-api' => 'EDDPaypalAdvanced', 'completed' => 'true'), get_permalink($this->success_page));

        if ($this->testmode) {
            $this->is_mode = 'TEST';
            $this->Paypal_URL = "https://pilot-payflowpro.paypal.com";
            $this->paypal_vendor = isset($this->edd_options['paypal_for_edd_api_advanced_sandbox_merchant']) ? trim($this->edd_options['paypal_for_edd_api_advanced_sandbox_merchant']) : '';
            $this->paypal_password = isset($this->edd_options['paypal_for_edd_api_advanced_sandbox_password']) ? trim($this->edd_options['paypal_for_edd_api_advanced_sandbox_password']) : '';
            $this->paypal_user = isset($this->edd_options['paypal_for_edd_api_advanced_sandbox_user']) ? trim($this->edd_options['paypal_for_edd_api_advanced_sandbox_user']) : '';
            $this->paypal_partner = isset($this->edd_options['paypal_for_edd_api_advanced_sandbox_partner']) ? trim($this->edd_options['paypal_for_edd_api_advanced_sandbox_partner']) : 'PayPal';
        } else {
            $this->is_mode = 'LIVE';
            $this->Paypal_URL = "https://payflowpro.paypal.com";
            $this->paypal_vendor = isset($this->edd_options['paypal_for_edd_api_advanced_live_merchant']) ? trim($this->edd_options['paypal_for_edd_api_advanced_live_merchant']) : '';
            $this->paypal_password = isset($this->edd_options['paypal_for_edd_api_advanced_live_password']) ? trim($this->edd_options['paypal_for_edd_api_advanced_live_password']) : '';
            $this->paypal_user = isset($this->edd_options['paypal_for_edd_api_advanced_live_user']) ? trim($this->edd_options['paypal_for_edd_api_advanced_live_user']) : '';
            $this->paypal_partner = isset($this->edd_options['paypal_for_edd_api_advanced_live_partner']) ? trim($this->edd_options['paypal_for_edd_api_advanced_live_partner']) : 'PayPal';
        }

        switch (strtoupper($this->edd_options['paypal_for_edd_api_advanced_layout'])) {
            case 'A': $this->layout = 'TEMPLATEA';
                break;
            case 'B': $this->layout = 'TEMPLATEB';
                break;
            case 'C': $this->layout = 'MINLAYOUT';
                break;
        }
        add_action('pal_for_edd_get_receipt_hook', array($this, 'paypal_for_edd_paypal_advanced_receipt_page'), 10, 1);
    }

    public function paypal_for_edd_paypal_advanced_process_payment($posted) {
        try {
            edd_clear_errors();
            $is_posted_array = $this->paypal_for_edd_paypal_advanced_is_posted_array($posted);
            if ($is_posted_array) {
                $this->paypal_for_edd_paypal_advanced_process($posted);
            } else {
                wp_redirect(get_permalink($this->purchase_page));
                exit;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_process($posted) {
        try {
            $this->Order_id = $this->paypal_for_edd_paypal_advanced_create_order($posted);
            $this->paypal_for_edd_paypal_advanced_update_post_meta($posted);
            $this->paypal_for_edd_paypal_advanced_secure_token();
            if ($this->securetoken != "") {
                update_post_meta($this->Order_id, '_secure_token_id', $this->secure_token_id);
                update_post_meta($this->Order_id, '_secure_token', $this->securetoken);
                $auto_generate_key = apply_filters('pal_for_edd_generate_order_key', uniqid('order_'));
                update_post_meta($this->Order_id, '_order_key', 'edd_' . $auto_generate_key);
                $pay_url = add_query_arg(array('order-pay' => $this->Order_id, 'key' => $auto_generate_key, 'edd-order-pay' => 'Receipt'), get_permalink($this->purchase_page));
                wp_redirect($pay_url);
                exit;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_update_post_meta($posted) {

        if (empty($this->Order_id)) {
            return;
        }
        $fees = 0;
        if (isset($posted['fees']) && count($posted['fees']) > 0) {

            foreach ($posted['fees'] as $key => $value) {
                $fees = $fees + $value['amount'];
            }
        }
        update_post_meta($this->Order_id, '_edd_payment_date', $posted['date']);
        update_post_meta($this->Order_id, '_edd_payment_fees', $fees);
        return;
    }

    public function paypal_for_edd_paypal_advanced_secure_token() {
        try {
            $order_details = array();
            $order_details = $this->paypal_for_edd_paypal_advanced_order_details('');
            if (is_array($order_details) && count($order_details) == '0') {
                return;
            }
            // Generate unique id
            $this->secure_token_id = uniqid(substr($_SERVER['HTTP_HOST'], 0, 9), true);

            $paypal_args = array();
            //override the layout with mobile template, if browsed from mobile if the exitsing layout is C or MINLAYOUT
            if (($this->layout == 'MINLAYOUT' || $this->layout == 'C') && $this->mobilemode == "yes") {
                $template = wp_is_mobile() ? "MOBILE" : $this->layout;
            } else {
                $template = $this->layout;
            }

            $this->order_key = substr(microtime(), -5);
            $order_personal_details = $order_details['user_info'];

            $paypal_args = array(
                'VERBOSITY' => 'HIGH',
                'USER' => $this->paypal_user,
                'VENDOR' => $this->paypal_vendor,
                'PARTNER' => $this->paypal_partner,
                'PWD[' . strlen($this->paypal_password) . ']' => $this->paypal_password,
                'SECURETOKENID' => $this->secure_token_id,
                'CREATESECURETOKEN' => 'Y',
                'TRXTYPE' => $this->paymentaction,
                'CUSTREF' => $this->order_key,
                'USER1' => $this->Order_id,
                'INVNUM' => $this->invoice_prifix . '' . $this->order_key,
                'AMT' => number_format($this->paypal_for_edd_paypal_advanced_total_AMT(), 2, '.', ''),
                'FREIGHTAMT' => number_format($this->paypal_for_edd_paypal_advanced_total_AMT(), 2, '.', ''),
                'COMPANYNAME[0]' => '',
                'CURRENCY' => edd_get_currency(),
                'EMAIL' => $this->paypal_for_edd_paypal_advanced_payer_EMAIL(),
                'BILLTOFIRSTNAME[' . strlen($order_personal_details['first_name']) . ']' => $order_personal_details['first_name'],
                'BILLTOLASTNAME[' . strlen($order_personal_details['last_name']) . ']' => $order_personal_details['last_name'],
                'BILLTOSTREET[' . strlen($order_personal_details['address']['line1'] . ' ' . $order_personal_details['address']['line2']) . ']' => $order_personal_details['address']['line1'] . ' ' . $order_personal_details['address']['line2'],
                'BILLTOCITY[' . strlen($order_personal_details['address']['city']) . ']' => $order_personal_details['address']['city'],
                'BILLTOSTATE[' . strlen($order_personal_details['address']['state']) . ']' => $order_personal_details['address']['state'],
                'BILLTOZIP' => $order_personal_details['address']['zip'],
                'BILLTOCOUNTRY[' . strlen($order_personal_details['address']['country']) . ']' => $order_personal_details['address']['country'],
                'BILLTOEMAIL' => $this->paypal_for_edd_paypal_advanced_payer_EMAIL(),
                'BILLTOPHONENUM' => '',
                'SHIPTOFIRSTNAME[' . strlen($order_personal_details['first_name']) . ']' => $order_personal_details['first_name'],
                'SHIPTOLASTNAME[' . strlen($order_personal_details['last_name']) . ']' => $order_personal_details['last_name'],
                'SHIPTOSTREET[' . strlen($order_personal_details['address']['line1'] . ' ' . $order_personal_details['address']['line2']) . ']' => $order_personal_details['address']['line1'] . ' ' . $order_personal_details['address']['line2'],
                'SHIPTOCITY[' . strlen($order_personal_details['address']['city']) . ']' => $order_personal_details['address']['city'],
                'SHIPTOZIP' => $order_personal_details['address']['zip'],
                'SHIPTOCOUNTRY[' . strlen($order_personal_details['address']['country']) . ']' => $order_personal_details['address']['country'],
                'BUTTONSOURCE' => 'mbjtechnolabs_SP',
                'RETURNURL[' . strlen($this->returnURL) . ']' => $this->returnURL,
                'URLMETHOD' => 'POST',
                'TEMPLATE' => $template,
                'PAGECOLLAPSEBGCOLOR' => ltrim($this->page_collapse_bgcolor, '#'),
                'PAGECOLLAPSETEXTCOLOR' => ltrim($this->page_collapse_textcolor, '#'),
                'PAGEBUTTONBGCOLOR' => ltrim($this->page_button_bgcolor, '#'),
                'PAGEBUTTONTEXTCOLOR' => ltrim($this->page_button_textcolor, '#'),
                'LABELTEXTCOLOR' => ltrim($this->label_textcolor, '#')
            );

            if (empty($order_personal_details['address']['state'])) {
                //replace with city
                $paypal_args['SHIPTOSTATE[' . strlen($order_personal_details['address']['city']) . ']'] = $order_personal_details['address']['city'];
            } else {
                //retain state
                $paypal_args['SHIPTOSTATE[' . strlen($order_personal_details['address']['state']) . ']'] = $order_personal_details['address']['state'];
            }

            // Determine the ERRORURL,CANCELURL and SILENTPOSTURL

            $cancelurl = add_query_arg('edd-advanced-api', 'EDDPaypalAdvanced', add_query_arg('cancel', 'true', $this->home_url));
            $paypal_args['CANCELURL[' . strlen($cancelurl) . ']'] = $cancelurl;

            $errorurl = add_query_arg('edd-advanced-api', 'EDDPaypalAdvanced', add_query_arg('error', 'true', $this->home_url));
            $paypal_args['ERRORURL[' . strlen($errorurl) . ']'] = $errorurl;

            $silentposturl = add_query_arg('edd-advanced-api', 'EDDPaypalAdvanced', add_query_arg('silent', 'true', $this->home_url));
            $paypal_args['SILENTPOSTURL[' . strlen($silentposturl) . ']'] = $silentposturl;

            $paypal_args['TAXAMT'] = $this->paypal_for_edd_paypal_advanced_total_TAX();
            $paypal_args['FEES'] = $this->paypal_for_edd_paypal_advanced_total_FEES();
            $paypal_args['ITEMAMT'] = 0;

            // Cart Contents
            $item_loop = 0;
            if (sizeof($order_details['cart_details']) > 0) {
                foreach ($order_details['cart_details'] as $key => $item) {
                    if ($item['quantity']) {

                        $paypal_args['L_NAME' . $item_loop . '[' . strlen($item['name']) . ']'] = $item['name'];
                        $paypal_args['L_QTY' . $item_loop] = $item['quantity'];
                        $paypal_args['L_COST' . $item_loop] = $item['price']; /* No Tax , No Round) */
                        $paypal_args['L_TAXAMT' . $item_loop] = $item['tax']; /* No Round it */
                        $paypal_args['ITEMAMT'] += $item['price']; /* No tax, No Round */
                        $item_loop++;
                    }
                }
            }


            $postData = '';
            $logData = '';

            foreach ($paypal_args as $key => $val) {

                $postData .='&' . $key . '=' . $val;
                if (strpos($key, 'PWD') === 0)
                    $logData .='&PWD=XXXX';
                else
                    $logData .='&' . $key . '=' . $val;
            }

            $postData = trim($postData, '&');


            $response = wp_remote_post($this->Paypal_URL, array(
                'method' => 'POST',
                'body' => $postData,
                'timeout' => 70,
                'user-agent' => 'pal-for-edd',
                'httpversion' => '1.1',
                'headers' => array('host' => 'www.paypal.com')
            ));

            if (is_wp_error($response)) {

                $this->paypal_for_edd_paypal_advanced_set_edd_error('paypal_advanced_request_responce', $response->get_error_message());
                $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'ERRORS', $response->get_error_message());
                $this->paypal_for_edd_paypal_advanced_redirect_page($this->purchase_page);
                exit;
            }
            parse_str($response['body'], $this->parsed_response);
            if (isset($this->parsed_response['RESPMSG']) && in_array($this->parsed_response['RESULT'], array(0, 126, 127))) {
                $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'REQUEST', $this->parsed_response);
                $this->securetoken = $this->parsed_response['SECURETOKEN'];
                $this->secure_token_id = $this->parsed_response['SECURETOKENID'];
                return;
            } else {
                $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'REQUEST', $this->parsed_response);
                $this->paypal_for_edd_paypal_advanced_set_edd_error('paypal_advanced_request_responce', $this->parsed_response['RESPMSG']);
                $this->paypal_for_edd_paypal_advanced_redirect_page($this->purchase_page);
                exit;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_create_order($purchase_data) {
        try {

            $payment_data = array(
                'subtotal' => $purchase_data['subtotal'],
                'price' => $purchase_data['price'],
                'date' => $purchase_data['date'],
                'fees' => $purchase_data['fees'],
                'discount' => $purchase_data['discount'],
                'tax' => $purchase_data['tax'],
                'user_email' => $purchase_data['user_email'],
                'purchase_key' => $purchase_data['purchase_key'],
                'currency' => edd_get_currency(),
                'downloads' => $purchase_data['downloads'],
                'user_info' => $purchase_data['user_info'],
                'post_data' => $purchase_data['post_data'],
                'cart_details' => $purchase_data['cart_details'],
                'gateway' => $purchase_data['gateway'],
                'status' => 'pending'
            );
            $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'POST', array($payment_data));
            return edd_insert_payment($payment_data);
        } catch (Exception $Ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_is_posted_array($posted) {
        try {
            $result = FALSE;
            if (is_array($posted) && count($posted) > 0) {
                $result = TRUE;
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_write_log($handle, $response_name, $result_array) {

        if (!$this->debug) {
            return;
        }

        foreach ($result_array as $key => $value) {
            if ($key === 'SECURETOKEN' || $key === 'SECURETOKENID') {
                $star = '';
                for ($i = 0; $i <= strlen($value); $i++) {
                    $star .= '*';
                }
                $result_array[$key] = $star;
            }
        }

        $log = new PayPal_For_EDD_Logger();
        $log->add($handle, $response_name . '=>' . print_r($result_array, true));
        return;
    }

    public function paypal_for_edd_paypal_advanced_order_details($Order_id = null) {
        try {
            $result = array();
            $result_array = array();
            $id = '';
            if (isset($Order_id) && !empty($Order_id)) {
                $id = $Order_id;
            } else {
                $id = $this->Order_id;
            }

            $result_array = get_post_meta($id, '_edd_payment_meta');
            if (count($result_array) > 0) {
                $result = $result_array[0];
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_total_AMT() {
        try {
            $result = '0';
            $result_array = array();
            $result_array = get_post_meta($this->Order_id, '_edd_payment_total');
            if (count($result_array) > 0) {
                $result = $result_array[0];
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_total_TAX() {
        try {
            $result = '0';
            $result_array = array();
            $result_array = get_post_meta($this->Order_id, '_edd_payment_tax');
            if (count($result_array) > 0) {
                $result = $result_array[0];
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_total_FEES() {
        try {
            $result = '0';
            $result_array = array();
            $result_array = get_post_meta($this->Order_id, '_edd_payment_fees');
            if (count($result_array) > 0) {
                $result = $result_array[0];
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_payer_EMAIL() {
        try {
            $result = '';
            $result_array = array();
            $result_array = get_post_meta($this->Order_id, '_edd_payment_user_email');
            if (count($result_array) > 0) {
                $result = $result_array[0];
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_response($posted) {
        try {
            if (!is_array($posted) && count($posted) == '0') {
                return;
            }
            $this->paypal_for_edd_paypal_advanced_relay_response();
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_relay_response() {
        try {
            //define a variable to indicate whether it is a silent post or return
            if (isset($_REQUEST['silent']) && $_REQUEST['silent'] == 'true')
                $silent_post = true;
            else
                $silent_post = false;

            //if valid request
            if (!isset($_REQUEST['INVOICE'])) { // Redirect to homepage, if any invalid request or hack
                if ($silent_post === false)
                    wp_redirect(home_url('/'));
                exit;
            }
            // get Order ID
            $order_id = $_REQUEST['USER1'];
            // Create order object
            $order = get_post($order_id);

            //check for the status of the order, if completed or processing, redirect to thanks page. This case happens when silentpost is on
            $status = isset($order->post_status) ? $order->post_status : 'pending';

            if ($status == 'processing' || $status == 'completed') {
                if ($silent_post === false) {
                    //edd_send_to_success_page();
                    $this->paypal_for_edd_paypal_advanced_redirect_to(get_permalink($this->success_page));
                }
            }

            if (isset($_REQUEST['cancel']) && $_REQUEST['cancel'] == 'true')
                $_REQUEST['RESULT'] = -1;

            //handle the successful transaction
            switch ($_REQUEST['RESULT']) {

                case 0 :
                    //handle exceptional cases
                    if ($_REQUEST['RESPMSG'] == 'Approved')
                        $this->paypal_for_edd_paypal_advanced_success_handler($order, $order_id, $silent_post);
                    else if ($_REQUEST['RESPMSG'] == 'Declined')
                        $this->paypal_for_edd_paypal_advanced_decline_handler($order, $order_id, $silent_post);
                    else
                        $this->paypal_for_edd_paypal_advanced_error_handler($order, $order_id, $silent_post);
                    break;
                case 12:
                    $this->paypal_for_edd_paypal_advanced_decline_handler($order, $order_id, $silent_post);
                    break;
                case -1:
                    $this->paypal_for_edd_paypal_advanced_cancel_handler();
                    break;
                default:
                    //handles error order
                    $this->paypal_for_edd_paypal_advanced_error_handler($order, $order_id, $silent_post);
                    break;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_error_handler($order, $order_id, $silent_post) {

        edd_clear_errors();
        // Add error
        $this->paypal_for_edd_paypal_advanced_set_edd_error('paypal_advanced_request_responce', urldecode($_POST['RESPMSG']));

        //redirect to the checkout page, if not silent post
        if ($silent_post === false)
            $this->paypal_for_edd_paypal_advanced_redirect_to(get_permalink($this->failure_page));
    }

    public function paypal_for_edd_paypal_advanced_success_handler($order, $order_id, $silent_post) {

        if (get_post_meta($order_id, '_secure_token', true) == $_REQUEST['SECURETOKEN']) {
            
        } else {
            //redirect to the checkout page, if not silent post
            if ($silent_post === false)
                $this->redirect_to($order->get_checkout_payment_url(true));
            exit;
        }

        $inq_result = $this->paypal_for_edd_paypal_advanced_inquiry_transaction($order, $order_id);

        // Handle response
        if ($inq_result == 'Approved') {//if approved
            // Payment complete
            //$order->payment_complete($_POST['PNREF']);
            edd_update_payment_status($order_id, 'publish');
            edd_empty_cart();
            if ($silent_post === false) {
                //edd_send_to_success_page();
                $this->paypal_for_edd_paypal_advanced_redirect_to(get_permalink($this->success_page));
            }
        }
    }

    public function paypal_for_edd_paypal_advanced_inquiry_transaction($order, $order_id) {

        $paypal_args = array(
            'USER' => $this->paypal_user,
            'VENDOR' => $this->paypal_vendor,
            'PARTNER' => $this->paypal_partner,
            'PWD[' . strlen($this->paypal_password) . ']' => $this->paypal_password,
            'ORIGID' => $_POST['PNREF'],
            'TENDER' => 'C',
            'TRXTYPE' => 'I',
            'BUTTONSOURCE' => 'mbjtechnolabs_SP'
        );

        $postData = ''; //stores the post data string
        foreach ($paypal_args as $key => $val) {
            $postData .='&' . $key . '=' . $val;
        }

        $postData = trim($postData, '&');

        /* Using Curl post necessary information to the Paypal Site to generate the secured token */
        $response = wp_remote_post($this->Paypal_URL, array(
            'method' => 'POST',
            'body' => $postData,
            'timeout' => 70,
            'user-agent' => 'pal-for-edd ',
            'httpversion' => '1.1',
            'headers' => array('host' => 'www.paypal.com')
        ));
        if (is_wp_error($response)) {
            $this->paypal_for_edd_paypal_advanced_set_edd_error('paypal_advanced_request_responce', $response->get_error_message());
            $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'ERRORS', $response->get_error_message());
            $this->paypal_for_edd_paypal_advanced_redirect_page($this->purchase_page);
            exit;
        }
        if (empty($response['body'])) {
            $this->paypal_for_edd_paypal_advanced_set_edd_error('paypal_advanced_request_responce', 'Empty response.');
            $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'ERRORS', 'Empty response.');
            $this->paypal_for_edd_paypal_advanced_redirect_page($this->purchase_page);
            exit;
        }

        /* Parse and assign to array */
        $inquiry_result_arr = array(); //stores the response in array format
        parse_str($response['body'], $inquiry_result_arr);

        if ($inquiry_result_arr['RESULT'] == 0 && $inquiry_result_arr['RESPMSG'] == 'Approved') {
            $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'TRANSACTION DETAILS', $inquiry_result_arr);
            return 'Approved';
        } else {
            $this->paypal_for_edd_paypal_advanced_write_log('paypal_for_edd_paypal_advanced', 'TRANSACTION ERRORS', $inquiry_result_arr);
            return 'Error';
        }
    }

    public function paypal_for_edd_paypal_advanced_cancel_handler() {
        wp_redirect(get_permalink($this->failure_page));
        exit;
    }

    public function paypal_for_edd_paypal_advanced_decline_handler($order, $order_id, $silent_post) {

        $order->update_status('failed', __('Payment failed via PayPal Advanced because of.', 'pal-for-edd') . '&nbsp;' . $_POST['RESPMSG']);
        $this->paypal_for_edd_paypal_advanced_error_handler($order, $order_id, $silent_post);
    }

    public function paypal_for_edd_paypal_advanced_redirect_to($redirect_url) {
        // Clean
        @ob_clean();

        // Header
        header('HTTP/1.1 200 OK');

        //redirect to the url based on layout type
        if ($this->layout != 'MINLAYOUT') {
            wp_redirect($redirect_url);
        } else {
            echo "<script>window.parent.location.href='" . $redirect_url . "';</script>";
        }
        exit;
    }

    public function paypal_for_edd_paypal_advanced_set_edd_error($error_title, $error_msg) {
        try {

            edd_set_error($error_title, __($error_msg, 'pal-for-edd'));
            return;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_redirect_page($page_id) {
        try {

            wp_redirect(get_permalink($page_id));
            return;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_advanced_receipt_page($posted) {

        //get the tokens
        $this->secure_token_id = get_post_meta($posted['order-pay'], '_secure_token_id', true);
        $this->securetoken = get_post_meta($posted['order-pay'], '_secure_token', true);

        //display the form in IFRAME, if it is layout C, otherwise redirect to paypal site
        if ($this->layout == 'MINLAYOUT' || $this->layout == 'C') {
            $location = 'https://payflowlink.paypal.com?mode=' . $this->is_mode . '&amp;SECURETOKEN=' . $this->securetoken . '&amp;SECURETOKENID=' . $this->secure_token_id;
            ?> 
            <iframe id="pal_for_edd_iframe" src="<?php echo $location; ?>" width="550" height="565" scrolling="no" frameborder="0" border="0" allowtransparency="true"></iframe>
            <?php
        } else {
            $location = 'https://payflowlink.paypal.com?mode=' . $this->is_mode . '&SECURETOKEN=' . $this->securetoken . '&SECURETOKENID=' . $this->secure_token_id;
            wp_redirect($location);
            exit;
        }
    }

    public function Paypal_For_EDD_PayPal_Advanced_Order_HTML($order_id) {
        try {
            $HTML_STRING = "";
            if (isset($order_id) && !empty($order_id)) {
                $HTML_STRING = apply_filters('pal_for_edd_order', sprintf('<ul class="order_details"><li class="order">%s :<strong>%s</strong></li><li class="date">%s :<strong>%s</strong></li><li class="total">%s :<strong><span class="amount"><span class="currencysymbol">%s</span>%s</span></strong></li><li class="method">%s :<strong>%s</strong></li></ul>', esc_html('Order Number'), esc_attr($order_id), esc_html('Date'), esc_attr(date("F j, Y", strtotime(get_post_meta($order_id, '_edd_payment_date', true)))), esc_html('Total'), esc_attr(edd_currency_symbol(edd_get_currency())), esc_attr(number_format(get_post_meta($order_id, '_edd_payment_total', true), 2, '.', '')), esc_html('Payment Method'), esc_html('PayPal Advanced')
                ));
            }
            return $HTML_STRING;
        } catch (Exception $ex) {
            
        }
    }

}
