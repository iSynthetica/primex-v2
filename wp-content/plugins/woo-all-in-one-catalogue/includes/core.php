<?php
// add_action( 'init', 'wooaioc_register_assets');
add_action('wp_enqueue_scripts', 'wooaioc_register_assets');

function wooaioc_register_assets() {
    wp_register_style('wooaioc-style', WOOAIOCATALOGUE_CSS_URL . '/style.css');
    wp_enqueue_style( 'wooaioc-style' );
}

function wooaioc_register_style( $handle, $src, $deps = array(), $media = 'all' ) {
    $ver      = time();
    wp_register_style( $handle, $src, $deps, $ver, $media );
    wp_enqueue_style( $handle );
}

function wooaioc_register_script( $handle, $src, $deps = array(), $has_i18n = true ) {
    $filename     = str_replace( plugins_url( '/', __DIR__ ), '', $src );
    $ver          = time();
    $deps_path    = dirname( __DIR__ ) . '/' . str_replace( '.js', '.deps.json', $filename );
    $dependencies = file_exists( $deps_path ) ? json_decode( file_get_contents( $deps_path ) ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
    $dependencies = array_merge( $dependencies, $deps );

    wp_register_script( $handle, $src, $dependencies, $ver, true );
//    if ( $has_i18n && function_exists( 'wp_set_script_translations' ) ) {
//        wp_set_script_translations( $handle, 'woocommerce', dirname( __DIR__ ) . '/languages' );
//    }
}
