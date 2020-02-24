<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<?php
global $woocommerce;

$chosen_shipping_rates = WC()->session->get('chosen_shipping_methods');
$local_pickup_city = get_option('snth_wc_np_city_dep');
$local_pickup_address = get_option('snth_wc_np_address_dep');

var_dump($chosen_shipping_rates);
var_dump($local_pickup_city);
var_dump($local_pickup_address);

?>
<script type="text/javascript">
    jQuery(function ($) {

    });
</script>
