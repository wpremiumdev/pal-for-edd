<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if( (isset($_GET['order-pay']) && !empty($_GET['order-pay'])) && (isset($_GET['key']) && !empty($_GET['key'])) ){    
$Paypal_For_EDD_PayPal_Advanced_Helper = new Paypal_For_EDD_PayPal_Advanced_Helper();
$Paypal_For_EDD_HTML = $Paypal_For_EDD_PayPal_Advanced_Helper->Paypal_For_EDD_PayPal_Advanced_Order_HTML($_GET['order-pay']);
?>
    <script type="text/javascript">
        jQuery('header h1.entry-title').html();
        jQuery('header h1.entry-title').html('Pay For Order');
    </script>
<div class="pal_for_edd_pay_for_order"> 
    <?php
    echo $Paypal_For_EDD_HTML;
    $is_receipt = is_receipt_page($_GET);
    if ($is_receipt) {
        do_action('pal_for_edd_get_receipt_hook', $_GET);
    } else {
        do_action('pal_for_edd_get_receipt_hook', $_GET);
    }
    ?>
</div>    
<?php } else {
    return;
}