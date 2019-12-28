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

require_once(SNTH_INCLUDES.'/wc-core.php');
require_once(SNTH_INCLUDES.'/wc-template-functions.php');
require_once(SNTH_INCLUDES.'/wc-template-hooks.php');