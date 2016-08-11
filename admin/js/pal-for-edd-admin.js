jQuery(function ($) {
    /**
     * Paypal Express checkout
     */
    
    jQuery('input[name="edd_settings[paypal_for_edd_advanced_page_collapse_bgcolor]"]').wpColorPicker();
    jQuery('input[name="edd_settings[paypal_for_edd_advanced_page_collapse_textcolor]"]').wpColorPicker();
    jQuery('input[name="edd_settings[paypal_for_edd_advanced_page_button_bgcolor]"]').wpColorPicker();
    jQuery('input[name="edd_settings[paypal_for_edd_advanced_page_button_textcolor]"]').wpColorPicker();
    jQuery('input[name="edd_settings[paypal_for_edd_advanced_label_textcolor]"]').wpColorPicker();
    
    jQuery('input[name="edd_settings[paypal_for_edd_ex_testmode]"]').change(function () {
        var sandbox = jQuery('input[name="edd_settings[paypal_for_edd_api_ex_sandbox_username]"], input[name="edd_settings[paypal_for_edd_api_ex_sandbox_password]"], input[name="edd_settings[paypal_for_edd_api_ex_sandbox_signature]"]').closest('tr'),
                production = jQuery('input[name="edd_settings[paypal_for_edd_api_ex_live_username]"], input[name="edd_settings[paypal_for_edd_api_ex_live_password]"], input[name="edd_settings[paypal_for_edd_api_ex_live_signature]"]').closest('tr');
        if (jQuery(this).is(':checked')) {
            sandbox.show();
            production.hide();
        } else {
            sandbox.hide();
            production.show();
        }
    }).change();

    if (jQuery('input[name="edd_settings[paypal_for_edd_ex_enabal_button]"]').is(':checked')) {
        jQuery('input[name="edd_settings[paypal_for_edd_ex_button_link]"]').closest('tr').show();
    } else {
        jQuery('input[name="edd_settings[paypal_for_edd_ex_button_link]"]').closest('tr').hide();
    }
    jQuery('input[name="edd_settings[paypal_for_edd_ex_enabal_button]"]').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery('input[name="edd_settings[paypal_for_edd_ex_button_link]"]').closest('tr').show();
        } else {
            jQuery('input[name="edd_settings[paypal_for_edd_ex_button_link]"]').closest('tr').hide();
        }
    }).change();


    /**
     * Paypal Pro
     */
    jQuery('input[name="edd_settings[paypal_for_edd_pro_testmode]"]').change(function () {
        var sandbox = jQuery('input[name="edd_settings[paypal_for_edd_api_pro_sandbox_username]"], input[name="edd_settings[paypal_for_edd_api_pro_sandbox_password]"], input[name="edd_settings[paypal_for_edd_api_pro_sandbox_signature]"]').closest('tr'),
                production = jQuery('input[name="edd_settings[paypal_for_edd_api_pro_live_username]"], input[name="edd_settings[paypal_for_edd_api_pro_live_password]"], input[name="edd_settings[paypal_for_edd_api_pro_live_signature]"]').closest('tr');
        if (jQuery(this).is(':checked')) {
            sandbox.show();
            production.hide();
        } else {
            sandbox.hide();
            production.show();
        }
    }).change();


    /**
     * Paypal Payflow
     */
    jQuery('input[name="edd_settings[paypal_for_edd_payflow_testmode]"]').change(function () {
        var sandbox = jQuery('input[name="edd_settings[paypal_for_edd_api_Payflow_sandbox_vendor]"], input[name="edd_settings[paypal_for_edd_api_payflow_sandbox_password]"], input[name="edd_settings[paypal_for_edd_api_payflow_sandbox_user]"], input[name="edd_settings[paypal_for_edd_api_payflow_sandbox_partner]"]').closest('tr'),
                production = jQuery('input[name="edd_settings[paypal_for_edd_api_Payflow_live_vendor]"], input[name="edd_settings[paypal_for_edd_api_payflow_live_password]"], input[name="edd_settings[paypal_for_edd_api_payflow_live_user]"], input[name="edd_settings[paypal_for_edd_api_payflow_live_partner]"]').closest('tr');
        if (jQuery(this).is(':checked')) {
            sandbox.show();
            production.hide();
        } else {
            sandbox.hide();
            production.show();
        }
    }).change();
    
    
    /**
     * Paypal Advanced
     */
    jQuery('input[name="edd_settings[paypal_for_edd_advanced_testmode]"]').change(function () {
        var sandbox = jQuery('input[name="edd_settings[paypal_for_edd_api_advanced_sandbox_merchant]"], input[name="edd_settings[paypal_for_edd_api_advanced_sandbox_password]"], input[name="edd_settings[paypal_for_edd_api_advanced_sandbox_user]"], input[name="edd_settings[paypal_for_edd_api_advanced_sandbox_partner]"]').closest('tr'),
        production = jQuery('input[name="edd_settings[paypal_for_edd_api_advanced_live_merchant]"], input[name="edd_settings[paypal_for_edd_api_advanced_live_password]"], input[name="edd_settings[paypal_for_edd_api_advanced_live_user]"], input[name="edd_settings[paypal_for_edd_api_advanced_live_partner]"]').closest('tr');
        if (jQuery(this).is(':checked')) {
            sandbox.show();
            production.hide();
        } else {
            sandbox.hide();
            production.show();
        }
    }).change();    
});