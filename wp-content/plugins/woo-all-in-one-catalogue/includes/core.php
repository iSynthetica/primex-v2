<?php
// add_action( 'init', 'wooaioc_register_assets');
add_action('wp_enqueue_scripts', 'wooaioc_register_assets');

function wooaioc_register_assets() {
    wp_register_style('wooaioc-style', WOOAIOCATALOGUE_CSS_URL . '/style.css');
    wp_enqueue_style( 'wooaioc-style' );

    wp_register_script('wooaioc-js', WOOAIOCATALOGUE_JS_URL . '/script.js', array('jquery'), '0.1', true);
    wp_enqueue_script( 'wooaioc-js' );

    wp_localize_script( 'wooaioc-js', 'wooaiocJsObj', array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce( 'snth_nonce' )
    ) );
}
