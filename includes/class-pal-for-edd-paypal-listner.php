<?php

/**
 * @class       PayPal_For_EDD_PayPal_listner
 * @version	1.0.0
 * @package	pal-for-edd
 * @category	Class
 * @author      @author     mbj-webdevelopment <mbjwebdevelopment@gmail.com>
 */
class PayPal_For_EDD_PayPal_listner {

    public function __construct() {

        $this->liveurl = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        $this->testurl = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
    }

    public function check_ipn_request() {
        @ob_clean();
        $ipn_response = !empty($_POST) ? $_POST : false;
        if ($ipn_response && $this->check_ipn_request_is_valid($ipn_response)) {
            header('HTTP/1.1 200 OK');
            return true;
        } else {
            return false;
        }
    }

    public function check_ipn_request_is_valid($ipn_response) {
        $is_sandbox = (isset($ipn_response['test_ipn'])) ? 'yes' : 'no';
        if ('yes' == $is_sandbox) {
            $paypal_adr = $this->testurl;
        } else {
            $paypal_adr = $this->liveurl;
        }
        $validate_ipn = array('cmd' => '_notify-validate');
        $validate_ipn += stripslashes_deep($ipn_response);
        $params = array(
            'body' => $validate_ipn,
            'sslverify' => false,
            'timeout' => 60,
            'httpversion' => '1.1',
            'compress' => false,
            'decompress' => false,
            'user-agent' => 'pal-for-edd/'
        );
        $response = wp_remote_post($paypal_adr, $params);
        if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {
            return true;
        }
        return false;
    }

    public function successful_request($IPN_status) {
        $ipn_response = !empty($_POST) ? $_POST : false;
        $ipn_response['IPN_status'] = ( $IPN_status == true ) ? 'Verified' : 'Invalid';
        $posted = stripslashes_deep($ipn_response);
        $this->ipn_response_data_handler($posted);
    }

    public function ipn_response_data_handler($posted = null) {
        global $wp;
        
        $log = new PayPal_For_EDD_Logger();
        $log->add('paypal_for_edd_post_callback', print_r($posted, true));
        
        if (isset($posted) && !empty($posted)) {
            if (isset($posted['txn_id'])) {                
                $paypal_txn_id = $posted['txn_id'];              
            } else {
                return false;                
            }
            if ($this->paypal_for_edd_exist_post_by_title($paypal_txn_id) != false) {                
                $post_id = $this->paypal_for_edd_exist_post_by_title($paypal_txn_id);  
                edd_update_payment_status($post_id, ($posted['payment_status'] == 'Completed') ? 'publish' : 'pending');   
                $log->add('paypal_for_edd_update_order_status','Order Id: '.$post_id.' '.' Transaction Id: '.$paypal_txn_id.' '.'Status: '.$posted['payment_status']);
            } 
        }
    }
    
    function paypal_for_edd_exist_post_by_title($ipn_txn_id) {
        global $wpdb;        
        $post_data = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s AND post_type LIKE %s ", $ipn_txn_id, 'edd_payment'));       
        if (empty($post_data)) {
            return false;
        } else {
            return $post_data[0];
        }
    }

}
