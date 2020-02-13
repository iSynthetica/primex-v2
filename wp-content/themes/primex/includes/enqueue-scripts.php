<?php
/**
 * Enqueue scripts and styles
 *
 * @package WordPress
 * @subpackage Prime-X
 * @since Prime-X 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function snth_enqueue_scripts() {
    global $wp_styles;

    $query_args = array(
        'family' => 'Open+Sans+Condensed:300,700',
        'subset' => 'cyrillic'
    );
    wp_register_style( 'google_fonts', add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );
    wp_enqueue_style('google_fonts');

    if ( defined( 'WP_PROD_ENV' ) && WP_PROD_ENV ) {
        $site_css = 'style.min.css';
        $site_js = 'scripts.min.js';
    } else {
        $site_css = 'style.css';
        $site_js = 'scripts.js';
    }

    // Adding scripts file in the footer
    wp_enqueue_script( 'site-js', SNTH_SCRIPTS_URL.'/'.$site_js, array( 'jquery' ), SNTH_VERSION . time(), true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', SNTH_STYLES_URL.'/'.$site_css, array(), SNTH_VERSION . time(), 'all' );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }

    wp_localize_script( 'site-js', 'snthAjaxObj', array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' )
    ) );
}

add_action('wp_enqueue_scripts', 'snth_enqueue_scripts', 999);

add_filter( 'woocommerce_enqueue_styles', '__return_false' );

function wooaio_prevent_styles($return) {
    return false;
}
add_filter( 'wooaiocoupon_enqueue_styles', 'wooaio_prevent_styles' );
add_filter( 'wooaiocurrency_enqueue_styles', 'wooaio_prevent_styles' );
