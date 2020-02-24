<?php
/**
 * Woocommerce Template Hooks
 *
 * @package Hookah/Includes/WC
 */

if ( ! defined( 'ABSPATH' ) ) exit;

remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
add_action( 'woocommerce_variable_add_to_cart', 'snth_wc_variable_add_to_cart', 30 );

add_filter('woocommerce_available_variation', 'snth_wc_available_variation', 20, 3);