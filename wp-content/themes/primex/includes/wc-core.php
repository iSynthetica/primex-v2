<?php
/**
 * Add Woocommerce support to theme
 */

// Update Cart Count After AJAX
add_filter( 'woocommerce_add_to_cart_fragments', 'snth_wc_cart_count_fragments', 10, 1 );

function snth_wc_cart_count_fragments( $fragments ) {
    $fragments['#top-cart'] = do_shortcode('[snth_cart_icon]');
    return $fragments;
}