<?php
/**
 * Helpers library functions
 *
 * @since      0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * wprshrtcd_is_plugin_activated.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wprshrtcd_is_plugin_activated( $plugin_folder, $plugin_file ) {
    if ( wprshrtcd_is_plugin_active_simple( $plugin_folder . '/' . $plugin_file ) ) {
        return true;
    } else {
        return wprshrtcd_is_plugin_active_by_file( $plugin_file );
    }
}

/**
 * wprshrtcd_is_plugin_active_simple.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wprshrtcd_is_plugin_active_simple( $plugin ) {
    return (
        in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
        ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
    );
}

/**
 * wprshrtcd_is_plugin_active_by_file.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wprshrtcd_is_plugin_active_by_file( $plugin_file ) {
    foreach ( wprshrtcd_get_active_plugins() as $active_plugin ) {
        $active_plugin = explode( '/', $active_plugin );

        if ( isset( $active_plugin[1] ) && $plugin_file === $active_plugin[1] ) {
            return true;
        }
    }

    return false;
}

/**
 * wprshrtcd_get_active_plugins.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  array
 */
function wprshrtcd_get_active_plugins() {
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

    if ( is_multisite() ) {
        $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
    }

    return $active_plugins;
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function wprshrtcd_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    $template = wprshrtcd_locate_template( $template_name, $template_path, $default_path );

    do_action( 'wprshrtcd_before_template_part', $template_name, $template_path, $args );

    if (!empty($args) && is_array($args)) {
        extract($args);
    }

    include $template;

    do_action( 'wprshrtcd_after_template_part', $template_name, $template_path, $args );
}

/**
 * Like wc_get_template, but returns the HTML instead of outputting.
 *
 * @see wc_get_template
 * @since 2.5.0
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function wprshrtcd_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    ob_start();
    wprshrtcd_get_template( $template_name, $args, $template_path, $default_path );
    return ob_get_clean();
}
/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function wprshrtcd_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = 'woocommerce/wprshrtcd';
    }

    if ( ! $default_path ) {
        $default_path = untrailingslashit( plugin_dir_path( WPRSHRTCD_FILE ) ) . '/templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    // Get default template/.
    if ( ! $template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return $template;
}

/**
 * Output the Review comments template.
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args Arguments.
 * @param int        $depth Depth.
 */
function wprshrtcd_comments( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment; // WPCS: override ok.
    wprshrtcd_get_template(
        'review/review.php',
        array(
            'comment' => $comment,
            'args'    => $args,
            'depth'   => $depth,
            'show_schema'   => $args['show_schema'],
        )
    );
}

function wprshrtcd_add_plugin_screen_link($links) {
    $links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=wprshrtcd-help')) . '">'. __( "How to use", 'woo-product-reviews-shrtcd' ) .'</a>';
    return $links;
}