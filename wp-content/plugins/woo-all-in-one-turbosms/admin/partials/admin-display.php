<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Turbosms
 * @subpackage Woo_All_In_One_Turbosms/admin/partials
 */
?>


<div class="wrap">
    <h1><?php _e('Woocommerce TurboSMS', 'woo-all-in-one-turbosms') ?></h1>

    <?php include(dirname(__FILE__) . '/admin-tabs.php'); ?>

    <?php include(dirname(__FILE__) . '/admin-content-'.$active_tab.'.php'); ?>
</div>