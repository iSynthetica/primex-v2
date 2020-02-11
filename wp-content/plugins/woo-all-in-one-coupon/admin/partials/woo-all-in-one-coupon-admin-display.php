<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Coupon
 * @subpackage Woo_All_In_One_Coupon/admin/partials
 */
?>

<div class="wrap">
    <h1><?php _e('Woocommerce Coupons', 'woo-all-in-one-coupon') ?></h1>

    <?php include(dirname(__FILE__) . '/tabs.php'); ?>

    <?php include(dirname(__FILE__) . '/content-'.$active_tab.'.php'); ?>
</div>
