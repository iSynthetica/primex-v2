<?php
/**
 *
 */
// 'wooaioservice_access_settings'
$users_rules = get_option('wooaio_user_discount_rules', false);
$discount_id = !empty($_GET['discount_id']) ? sanitize_text_field($_GET['discount_id']) : false;

if ($discount_id) {
    include(dirname(__FILE__) . '/woo-all-in-one-discount-admin-content-users-edit.php');
} else {
    include(dirname(__FILE__) . '/woo-all-in-one-discount-admin-content-users-list.php');
}
?>