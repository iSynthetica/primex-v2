<?php
/**
 * Media functions Library
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Theme setup for custom post types
 */
function snth_add_image_sizes()
{
    $sizes = apply_filters( 'snth_image_sizes', array() );

    if (empty($sizes)) {
        return;
    }

    foreach ($sizes as $size => $value) {
        add_image_size(
            $size, $value['w'], $value['h'], $value['c']
        );
    }
}
add_action( 'init', 'snth_add_image_sizes', 999 );

/**
 * Display custom sizes in media select
 *
 * @param $sizes
 * @return array
 */
function snth_add_image_size_choose( $default_sizes )
{
    $sizes = apply_filters( 'snth_image_sizes', array() );

    if (empty($sizes)) {
        return $default_sizes;
    }

    foreach ($sizes as $size => $value) {
        $new_size = array($size => $value['label']);
        $default_sizes = $default_sizes + $new_size;
        //array_merge( $default_sizes, $new_size);
    }

    return $default_sizes;
}
add_filter( 'image_size_names_choose', 'snth_add_image_size_choose', 999 );

/**
 * Remove Default image sizes
 *
 * @param $sizes
 *
 * @return mixed
 */
function snth_remove_image_sizes( $default_sizes )
{
    $sizes = apply_filters( 'snth_image_sizes_to_remove', array() );

    if (empty($sizes)) {
        return $default_sizes;
    }

    foreach ($sizes as $size) {
        if(!empty( $default_sizes[$size] )) {
            unset( $default_sizes[$size] );
        }
    }

    return $default_sizes;
}
add_filter('intermediate_image_sizes_advanced', 'snth_remove_image_sizes');

/**
 * See all registered image sizes
 *
 * This function should not be used in production env
 * it's just for checking all registered image sizes
 *
 * @return array
 */
function _joints_get_all_image_sizes()
{
    global $_wp_additional_image_sizes;

    $default_image_sizes = array('thumbnail', 'medium', 'large');

    foreach ($default_image_sizes as $size) {
        $image_sizes[$size]['width'] = intval(get_option("{$size}_size_w"));
        $image_sizes[$size]['height'] = intval(get_option("{$size}_size_h"));
        $image_sizes[$size]['crop'] = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
    }

    if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
        $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
    }

    return $image_sizes;
}

//add_action( 'shutdown', function(){
//	print '<pre>';
//	print_r( _joints_get_all_image_sizes() );
//	print '</pre>';
//});