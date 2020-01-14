<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/admin/partials
 *
 * @var $active_tab
 */
?>

<div class="wrap">
    <h1><?php _e('Woocommerce Multicurrency', 'woo-all-in-one-currency') ?></h1>

    <?php include(dirname(__FILE__) . '/woo-all-in-one-currency-admin-tabs.php'); ?>

    <?php include(dirname(__FILE__) . '/woo-all-in-one-currency-admin-content-'.$active_tab.'.php'); ?>
</div>
