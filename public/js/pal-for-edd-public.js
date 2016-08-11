jQuery(function($) {

    jQuery('.paypal_for_edd_express_submit_span').unblock();
    jQuery(document).on('click', '.paypal_for_edd_express_submit', function() {
        jQuery('.paypal_for_edd_express_submit_span').block({message: null, overlayCSS: {background: '#ededed', opacity: 0.6}});

    });
    jQuery(document).on('click', '.paypal_for_edd_express_cancel', function(e) {
        e.preventDefault();
        jQuery('.paypal_for_edd_express_cancel').block({message: null, overlayCSS: {background: '#ededed', opacity: 0.6}});        
        var loadpage = jQuery(this).attr('href');
        if (typeof paypal_for_edd_checkout === 'undefined') {
            return false;
        }
        var data = {
            action: 'paypal_for_edd_cancel_payment',
            security: paypal_for_edd_checkout.paypal_for_edd_check_out,
            value: ''
        };

        jQuery.post(paypal_for_edd_checkout.ajax_url, data, function(data) {
            jQuery('.paypal_for_edd_express_cancel').unblock();
            window.location.href = loadpage;
        });
    });

    jQuery(document).on('click', '#edd_purchase_form #edd_express_checkout_button', function(e) {
        e.preventDefault();
        jQuery('#edd_show_buttons').block({message: '<img src="' + base_url + 'public/images/paypal-loader.gif"/>', overlayCSS: {background: '#ededed', opacity: 0.6}});        
        var t = document.getElementById("edd_purchase_form");
        if (typeof paypal_for_edd_checkout === 'undefined') {
            return false;
        }
        var data = {
            action: 'paypal_for_edd_process_to_payment',
            security: paypal_for_edd_checkout.paypal_for_edd_check_out,
            value: ''
        };

        jQuery.post(paypal_for_edd_checkout.ajax_url, data, function(data) {
            window.location.href = data;
            jQuery('#edd_show_buttons').unblock();
        });
    });

    var is_available = jQuery('#edd_show_full_shipping_details').is('[id]');
    is_place_order(is_available);

    function is_place_order(is_available) {

        if (is_available) {
            var form_action_url = jQuery('#form_action_url').val();
            if (form_action_url.toString().length > 0) {
                jQuery('#edd_show_buttons, #edd_payment_mode_select, #edd_payment_mode_submit, #edd_purchase_form_wrap, #edd_discount_code, .edd_cart_remove_item_btn, #edd_checkout_user_info, #edd_purchase_submit').remove();
                jQuery('.edd_form').attr('id', 'edd_purchase_place_order');
                jQuery('.edd_form').attr('action', form_action_url);
                jQuery('#edd_purchase_place_order').removeClass('edd_form');
            }
        } else {
            jQuery('#edd_show_buttons, #edd_payment_mode_select, #edd_payment_mode_submit, #edd_purchase_form_wrap').show();
        }

    }
});