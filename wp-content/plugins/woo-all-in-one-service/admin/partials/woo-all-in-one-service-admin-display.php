<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/admin/partials
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */
?>

<div class="wrap">
    <h1><?php _e('Woocommerce Repairs', 'woo-all-in-one-service') ?></h1>

    <?php include(dirname(__FILE__) . '/woo-all-in-one-service-admin-tabs.php'); ?>

    <?php include(dirname(__FILE__) . '/woo-all-in-one-service-admin-content-'.$active_tab.'.php'); ?>
</div>
