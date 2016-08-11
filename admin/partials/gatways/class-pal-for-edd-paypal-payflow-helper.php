<?php

ob_start();

class Paypal_For_EDD_PayPal_Payflow_Helper {

    public function __construct() {

        global $edd_options;

        $this->id = 'paypal_for_edd_PayPal_Payflow';
        $this->edd_options = $edd_options;
        $this->api_version = '120';
        $this->testmode = $test_mode = ( $test_mode = ( edd_is_test_mode() ) ? TRUE : ( isset($this->edd_options['paypal_for_edd_payflow_testmode']) ? TRUE : FALSE ) );
        $this->debug = isset($this->edd_options['paypal_for_edd_payflow_debug']) ? TRUE : FALSE;
        $this->paymentaction = isset($this->edd_options['paypal_for_edd_api_payflow_live_action']) ? $this->edd_options['paypal_for_edd_api_payflow_live_action'] : '';

        $this->purchase_page = isset($this->edd_options['purchase_page']) ? $this->edd_options['purchase_page'] : '';
        $this->success_page = isset($this->edd_options['success_page']) ? $this->edd_options['success_page'] : '';
        $this->failure_page = isset($this->edd_options['failure_page']) ? $this->edd_options['failure_page'] : '';
        $this->invoice_prifix = isset($this->edd_options['paypal_for_edd_payflow_invoice_id_prefix']) ? $this->edd_options['paypal_for_edd_payflow_invoice_id_prefix'] : '';
        $this->parsed_response = '';
        $this->parsed_response_personal_details = '';
        $this->paypal_for_edd_notifyurl = site_url('?Paypal_For_Edd&action=ipn_handler');
        if ($this->testmode) {
            $this->Paypal_URL = "https://pilot-payflowpro.paypal.com";
            $this->paypal_vendor = isset($this->edd_options['paypal_for_edd_api_Payflow_sandbox_vendor']) ? trim($this->edd_options['paypal_for_edd_api_Payflow_sandbox_vendor']) : '';
            $this->paypal_password = isset($this->edd_options['paypal_for_edd_api_payflow_sandbox_password']) ? trim($this->edd_options['paypal_for_edd_api_payflow_sandbox_password']) : '';
            $this->paypal_user = isset($this->edd_options['paypal_for_edd_api_payflow_sandbox_user']) ? trim($this->edd_options['paypal_for_edd_api_payflow_sandbox_user']) : '';
            $this->paypal_partner = isset($this->edd_options['paypal_for_edd_api_payflow_sandbox_partner']) ? trim($this->edd_options['paypal_for_edd_api_payflow_sandbox_partner']) : 'PayPal';
        } else {
            $this->Paypal_URL = "https://payflowpro.paypal.com";
            $this->paypal_vendor = isset($this->edd_options['paypal_for_edd_api_Payflow_live_vendor']) ? trim($this->edd_options['paypal_for_edd_api_Payflow_live_vendor']) : '';
            $this->paypal_password = isset($this->edd_options['paypal_for_edd_api_payflow_live_password']) ? trim($this->edd_options['paypal_for_edd_api_payflow_live_password']) : '';
            $this->paypal_user = isset($this->edd_options['paypal_for_edd_api_payflow_live_user']) ? trim($this->edd_options['paypal_for_edd_api_payflow_live_user']) : '';
            $this->paypal_partner = isset($this->edd_options['paypal_for_edd_api_payflow_live_partner']) ? trim($this->edd_options['paypal_for_edd_api_payflow_live_partner']) : 'PayPal';
        }
    }

    public function paypal_for_edd_paypal_payflow_process_payment($posted) {
        try {

            edd_clear_errors();
            $is_posted_array = $this->paypal_for_edd_paypal_payflow_is_posted_array($posted);
            if ($is_posted_array) {
                $is_posted_card_info = $this->paypal_for_edd_paypal_payflow_card_info($posted['card_info']);
                if ($is_posted_card_info) {

                    $is_post_data = $this->paypal_for_edd_paypal_payflow_post_data($posted);
                    $is_responce = $this->paypal_for_edd_paypal_payflow_request($is_post_data);
                    if (is_wp_error($is_responce)) {
                        $this->paypal_for_edd_paypal_payflow_set_edd_error('paypal_payflow_request_responce', $is_responce->get_error_message());
                        $this->paypal_for_edd_paypal_payflow_write_log('paypal_for_edd_paypal_payflow', 'ERRORS', $is_responce->get_error_message());
                        $this->paypal_for_edd_paypal_payflow_redirect_page($this->purchase_page);
                        exit;
                    }
                    if (isset($is_responce['body']) && !empty($is_responce['body'])) {
                        parse_str($is_responce['body'], $this->parsed_response);
                    }
                    if (isset($this->parsed_response['RESPMSG']) && in_array($this->parsed_response['RESULT'], array(0, 126, 127))) {
                        $this->paypal_for_edd_paypal_payflow_write_log('paypal_for_edd_paypal_payflow', 'REQUEST', $this->parsed_response);
                        $this->paypal_for_edd_paypal_payflow_insert_payment($posted);
                        exit;
                    } else {
                        $this->paypal_for_edd_paypal_payflow_write_log('paypal_for_edd_paypal_payflow', 'REQUEST', $this->parsed_response);
                        $this->paypal_for_edd_paypal_payflow_set_edd_error('paypal_payflow_request_responce', $this->parsed_response['RESPMSG']);
                        $this->paypal_for_edd_paypal_payflow_redirect_page($this->purchase_page);
                        exit;
                    }
                } else {
                    $this->paypal_for_edd_paypal_payflow_set_edd_error('paypal_payflow_card_empty', 'Credit Card Info is Empty!');
                    $this->paypal_for_edd_paypal_payflow_redirect_page($this->purchase_page);
                    exit;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_is_posted_array($posted) {
        try {
            $result = FALSE;
            if (is_array($posted) && count($posted) > 0) {
                $result = TRUE;
            }
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_card_info($card_info) {
        try {

            $result = TRUE;
            $key_array = array('card_name' => 'card_name', 'card_number' => 'card_number', 'card_cvc' => 'card_cvc', 'card_exp_month' => 'card_exp_month', 'card_exp_year' => 'card_exp_year');
            foreach ($card_info as $key => $value) {
                if (array_key_exists($key, $key_array)) {
                    if (isset($value) && empty($value)) {
                        $result = FALSE;
                        break;
                    }
                }
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_post_data($posted) {
        try {
            return $this->paypal_for_edd_paypal_payflow_get_post_array($posted);
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_get_post_array($posted) {
        try {

            $is_post_data = $this->paypal_for_edd_paypal_payflow_post_details($posted['post_data']);
            $is_card_info = $this->paypal_for_edd_paypal_payflow_card_details($posted['card_info']);

            $result = array(
                'USER' => $this->paypal_user,
                'VENDOR' => $this->paypal_vendor,
                'PARTNER' => $this->paypal_partner,
                'PWD' => $this->paypal_password,
                'TENDER' => 'C',
                'TRXTYPE' => $this->paymentaction,
                'AMT' => number_format(( $posted['price']), 2, '.', ''),
                'CURRENCY' => edd_get_currency(),
                'CUSTIP' => $this->paypal_for_edd_paypal_payflow_user_ip(),
                'EMAIL' => $is_post_data['edd_email'],
                'ACCT' => $is_card_info['card_number'],
                'EXPDATE' => sprintf('%02d', $is_card_info['card_exp_month']) . '' . $is_card_info['card_exp_year'],
                'CVV2' => $is_card_info['card_cvc'],
                'INVNUM' => $this->invoice_prifix . '' . substr(microtime(), -5),
                'FIRSTNAME' => $is_post_data['edd_first'],
                'LASTNAME' => $is_post_data['edd_last'],
                'DESC' => '',
                'NOTIFYURL' => $this->paypal_for_edd_notifyurl,
                'BUTTONSOURCE' => 'mbjtechnolabs_SP'
            );
            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_post_details($post_data) {
        try {

            $result = array();

            foreach ($post_data as $key => $value) {
                $result[$key] = $value;
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_card_details($card_info) {
        try {

            $result = array();

            foreach ($card_info as $key => $value) {
                $result[$key] = $value;
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_user_ip() {
        return !empty($_SERVER['HTTP_X_FORWARD_FOR']) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
    }

    public function paypal_for_edd_paypal_payflow_request($is_post_data) {
        try {
            return wp_remote_post($this->Paypal_URL, array('method' => 'POST', 'headers' => array('PAYPAL-NVP' => 'Y'), 'body' => $is_post_data, 'timeout' => 70, 'user-agent' => 'credit card payflow', 'httpversion' => '1.1'));
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_set_edd_error($error_title, $error_msg) {
        try {

            edd_set_error($error_title, __($error_msg, 'pal-for-edd'));
            return;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_write_log($handle, $response_name, $result_array) {

        if ($this->debug == false) {
            return;
        }
        $log = new PayPal_For_EDD_Logger();
        $log->add($handle, $response_name . '=>' . print_r($result_array, true));
        return;
    }

    public function paypal_for_edd_paypal_payflow_redirect_page($page_id) {
        try {

            wp_redirect(get_permalink($page_id));
            return;
        } catch (Exception $ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_insert_payment($purchase_data) {

        try {

            $is_transaction_info = $this->paypal_for_edd_paypal_payflow_transaction_details();

            $payment_data = array(
                'price' => $purchase_data['price'],
                'date' => $purchase_data['date'],
                'user_email' => $purchase_data['user_email'],
                'purchase_key' => $purchase_data['purchase_key'],
                'currency' => edd_get_currency(),
                'downloads' => $purchase_data['downloads'],
                'user_info' => $purchase_data['user_info'],
                'cart_details' => $purchase_data['cart_details'],
                'status' => 'pending'
            );
            $payment = edd_insert_payment($payment_data);

            if ($payment) {
                edd_update_payment_status($payment, $is_transaction_info['RESPMSG'] == 'Approved' ? 'publish' : 'pending');
                wp_update_post(array('ID' => $payment, 'post_title' => $this->parsed_response['PNREF']));
                edd_empty_cart();
                edd_send_to_success_page();
            } else {
                edd_record_gateway_error(__('Payment Error', 'easy-digital-downloads'), sprintf(__('Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'easy-digital-downloads'), json_encode($payment_data)), $payment);
                edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
            }
        } catch (Exception $Ex) {
            
        }
    }

    public function paypal_for_edd_paypal_payflow_transaction_details() {

        try {
            $result = array(
                'USER' => $this->paypal_user,
                'VENDOR' => $this->paypal_vendor,
                'PARTNER' => $this->paypal_partner,
                'PWD' => $this->paypal_password,
                'TRXTYPE' => 'I',
                'ORIGID' => $this->parsed_response['PNREF']
            );
            $is_responce = wp_remote_post($this->Paypal_URL, array('method' => 'POST', 'headers' => array('PAYPAL-NVP' => 'Y'), 'body' => $result, 'timeout' => 70, 'user-agent' => 'credit card Payflow', 'httpversion' => '1.1'));

            if (is_wp_error($is_responce)) {
                $this->paypal_for_edd_paypal_payflow_set_edd_error('paypal_payflow_request_responce', $is_responce->get_error_message());
                $this->paypal_for_edd_paypal_payflow_redirect_page($this->purchase_page);
                exit;
            }

            if (isset($is_responce['body']) && !empty($is_responce['body'])) {
                parse_str($is_responce['body'], $this->parsed_response_personal_details);
            }

            if (isset($this->parsed_response['RESPMSG']) && in_array($this->parsed_response_personal_details['RESULT'], array(0, 126, 127))) {
                $this->paypal_for_edd_paypal_payflow_write_log('paypal_for_edd_paypal_payflow', 'TRANSACTION', $this->parsed_response_personal_details);
                return $this->parsed_response_personal_details;
            } else {
                $this->paypal_for_edd_paypal_payflow_write_log('paypal_for_edd_paypal_payflow', 'TRANSACTION', $this->parsed_response_personal_details);
                return false;
            }
        } catch (Exception $Ex) {
            
        }
    }

}
ob_flush();