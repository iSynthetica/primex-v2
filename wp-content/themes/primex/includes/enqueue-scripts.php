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

    if ( defined( 'WP_PROD_ENV' ) && WP_PROD_ENV ) {
        $site_css = 'style.min.css';
        $site_js = 'scripts.min.js';
    } else {
        $site_css = 'style.css';
        $site_js = 'scripts.js';
    }

    // Adding scripts file in the footer
    wp_enqueue_script( 'site-js', SNTH_SCRIPTS_URL.'/'.$site_js, array( 'jquery' ), SNTH_VERSION . time(), true );
    wp_enqueue_script( 'plugins-js', SNTH_SCRIPTS_URL.'/plugins.js', array( 'jquery' ), SNTH_VERSION . time(), true );
    wp_enqueue_script( 'functions-js', SNTH_SCRIPTS_URL.'/functions.js', array( 'jquery' ), SNTH_VERSION . time(), true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', SNTH_STYLES_URL.'/'.$site_css, array(), SNTH_VERSION . time(), 'all' );
    wp_enqueue_style( 'canvas-css', SNTH_STYLES_URL.'/canvas.css', array(), SNTH_VERSION . time(), 'all' );
    wp_enqueue_style( 'swiper-css', SNTH_STYLES_URL.'/swiper.css', array(), SNTH_VERSION . time(), 'all' );
    wp_enqueue_style( 'dark-css', SNTH_STYLES_URL.'/dark.css', array(), SNTH_VERSION . time(), 'all' );
    wp_enqueue_style( 'animate-css', SNTH_STYLES_URL.'/animate.css', array(), SNTH_VERSION . time(), 'all' );
    wp_enqueue_style( 'magnific-popup-css', SNTH_STYLES_URL.'/magnific-popup.css', array(), SNTH_VERSION . time(), 'all' );
    wp_enqueue_style( 'responsive-css', SNTH_STYLES_URL.'/responsive.css', array(), SNTH_VERSION . time(), 'all' );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

add_action('wp_enqueue_scripts', 'snth_enqueue_scripts', 999);
