<?php
/**
 *
 */
// 'wooaioservice_access_settings'
$discount_rules = get_option('wooaio_product_discount_rules', false);
$discount_id = !empty($_GET['discount_id']) ? sanitize_text_field($_GET['discount_id']) : false;

if ($discount_id) {
    include(dirname(__FILE__) . '/woo-all-in-one-discount-admin-content-discounts-edit.php');
} else {
    include(dirname(__FILE__) . '/woo-all-in-one-discount-admin-content-discounts-list.php');
}
?>