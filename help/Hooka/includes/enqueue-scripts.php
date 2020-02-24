<?php
/**
 * Enqueue scripts and styles
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function snth_enqueue_scripts() {
  global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

    $query_args = array(
        'family' => 'Open+Sans:300,400,400i,700,700i|Oswald:300,400,700',
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
    wp_register_script( 'snth_wc-add-to-cart-variation', SNTH_SCRIPTS_URL.'/add-to-cart-variation.js', array( 'jquery', 'wp-util' ), SNTH_VERSION, true );

    wc_get_template( 'single-product/add-to-cart/variation.php' );

    $params = array(
        'wc_ajax_url'                      => WC_AJAX::get_endpoint( '%%endpoint%%' ),
        'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
        'i18n_make_a_selection_text'       => esc_attr__( 'Please select some product options before adding this product to your cart.', 'woocommerce' ),
        'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
    );

    wp_localize_script( 'snth_wc-add-to-cart-variation', 'wc_add_to_cart_variation_params', $params);

    // Adding scripts file in the footer
    wp_enqueue_script( 'site-js', SNTH_SCRIPTS_URL.'/'.$site_js, array( 'jquery' ), SNTH_VERSION, true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', SNTH_STYLES_URL.'/'.$site_css, array(), SNTH_VERSION, 'all' );

    // Comment reply script for threaded comments
    if ( is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
      wp_enqueue_script( 'comment-reply' );
    }

    wp_register_script('gmap', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDBeGNjLt_srVFXjDjduGyHtGu-fzn_Pt4');
    wp_register_script('gmap3', SNTH_VENDORS_URL . '/gmap/gmap3.min.js', array( 'gmap' ), SNTH_VERSION, true);
    wp_register_script('infobox', SNTH_VENDORS_URL . '/gmap/InfoBox/infobox.js', array( 'gmap' ), '', true );
    wp_register_script('markerclusterer', SNTH_VENDORS_URL . '/gmap/js-marker-clusterer/markerclusterer.js', array( 'gmap' ), '', true );
    wp_register_script('gmapTheme', SNTH_SCRIPTS_URL . '/gmap.js', array( 'gmap3' ), SNTH_VERSION, true);

    wp_register_script('gmapLocations', SNTH_SCRIPTS_URL . '/gmapLocations.js', array( 'gmap3', 'infobox', 'markerclusterer' ), SNTH_VERSION, true);

    //if ( is_checkout() ) {
    if ( false ) {
        global $woocommerce;

        $chosen_shipping_rates = WC()->session->get('chosen_shipping_methods');
        $local_pickup_city = get_option('snth_wc_np_city_dep');
        $local_pickup_address = get_option('snth_wc_np_address_dep');

        wp_enqueue_script('np-checkout', SNTH_SCRIPTS_URL.'/np-checkout.js', array( 'jquery', 'woocommerce', 'site-js' ), SNTH_VERSION, true);

        wp_localize_script( 'np-checkout', 'npCheckoutObj', array(
            'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
            'chosen_shipping_rates'     => $chosen_shipping_rates[0],
            'local_pickup_city'         => $local_pickup_city,
            'local_pickup_address'      => $local_pickup_address,
            'messages'                  => array(
                'first_text_select_city' => __('Select a City','snthwp'),
                'first_text_warhouse_no_city' => __('At first Select City','snthwp'),
                'first_text_warhouse_select_np' => __('Select Warhouse','snthwp'),
                'first_text_warhouse_pickup' => __('Local Pickup','snthwp'),
            ),
        ) );
    }

    wp_localize_script( 'site-js', 'snthWpJsObj', array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce( 'snth_nonce' ),
        'wooNonce'      => wp_create_nonce( 'snth_woo_nonce' ),
    ) );




    wp_enqueue_script( 'test' );

    // You can add inline scripts in this way
//	if ( is_page_template( 'page-templates/full-width.php' )) {
//		snth_inline_styles();
//		snth_inline_scripts();
//	}
}
add_action('wp_enqueue_scripts', 'snth_enqueue_scripts', 999);

function snth_inline_styles() {
    ob_start();
    ?>
    <style>
        body {
            background-color: #0a0a0a;
        }
    </style>
    <?php
    wp_add_inline_style( 'site-css', snth_clean_inline_css(ob_get_clean()) );
}

function snth_inline_scripts() {
    ob_start();
    ?>
    <script>
        console.log('Loaded');
    </script>
    <?php

    wp_add_inline_script( 'site-js', snth_clean_inline_js(ob_get_clean()) );
}

function snth_clean_inline_css( $custom_css )
{
    $custom_css = str_replace("<style>", "", $custom_css);
    $custom_css = str_replace("</style>", "", $custom_css);
    return $custom_css;
}

function snth_clean_inline_js( $custom_js )
{
    $custom_js = str_replace("<script>", "", $custom_js);
    $custom_js = str_replace("</script>", "", $custom_js);
    return $custom_js;
}