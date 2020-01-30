<?php

$currency_id = !empty($_GET['currency_code']) ? sanitize_text_field($_GET['currency_code']) : false;

if ($currency_id) {
    include(dirname(__FILE__) . '/woo-all-in-one-discount-admin-content-currency-edit.php');
} else {
    include(dirname(__FILE__) . '/woo-all-in-one-discount-admin-content-currency-list.php');
}
?>
