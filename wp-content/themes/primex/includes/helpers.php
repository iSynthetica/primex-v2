<?php
/**
 * Helpers library
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get current request type
 *
 * @param $type
 *
 * @return bool
 */
function snth_is_request( $type )
{
    switch ( $type ) {
        case 'admin' :
            return is_admin();
        case 'ajax' :
            return defined( 'DOING_AJAX' );
        case 'cron' :
            return defined( 'DOING_CRON' );
        case 'frontend' :
            return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
    }
}

/**
 * Show templates passing attributes and including the file.
 *
 * @param string $template_name
 * @param array $args
 * @param string $template_path
 */
function snth_show_template($template_name, $args = array(), $template_path = 'parts')
{
    if (!empty($args) && is_array($args)) {
        extract($args);
    }

    $located = snth_locate_template($template_name, $template_path);

    if (!file_exists($located)) {
        return;
    }

    include($located);
}

/**
 * Like show, but returns the HTML instead of outputting.
 *
 * @param $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 *
 * @return string
 */
function snth_get_template($template_name, $args = array(), $template_path = 'parts')
{
    ob_start();
    snth_show_template($template_name, $args, $template_path);
    return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * @param $template_name
 * @param string $template_path
 * @return string
 */
function snth_locate_template($template_name, $template_path = 'parts')
{
    if (!$template_path) {
        $template_path = 'parts';
    }

    $template = locate_template(
        array(
            trailingslashit($template_path) . $template_name,
            $template_name
        )
    );

    return $template;
}

/**
 * Checks if a plugin is activated
 *
 * @link https://codex.wordpress.org/Function_Reference/is_plugin_active
 * @param $plugin
 *
 * @return mixed
 */
function snth_is_plugin_active( $plugin )
{
    // TODO: Check what is wrong with this condition
    //if ( snth_is_request( 'frontend' ) ) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    //}

    return is_plugin_active( $plugin );
}

/**
 * Checks if a woocommerce is activated
 *
 * @return mixed
 */
function snth_is_woocommerce_active()
{
    return snth_is_plugin_active ( 'woocommerce/woocommerce.php' );
}

/**
 * Checks if a woocommerce is activated
 *
 * @return mixed
 */
function snth_is_yoast_seo_active()
{
    return snth_is_plugin_active ( 'wordpress-seo/wp-seo.php' );
}
