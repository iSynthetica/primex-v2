<?php
/**
 * Add Woocommerce support to theme
 */
function snth_wc_support()
{
    add_theme_support( 'woocommerce' );
    // add_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'snth_wc_support' );