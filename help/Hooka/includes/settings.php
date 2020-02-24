<?php
/**
 * Theme settings
 *
 * @package  Jointswp/Includes
 */

defined( 'ABSPATH' ) || exit;

function snth_settings_theme_logos ( $logos ) {
    return array(
        'alt_logo' => array(
            'label' => __('Alternative Logo', 'jointswp'),
            'description' => __('Alternative Logo for using f.e. on other BG color', 'jointswp'),
        ),
        'footer_logo' => array(
            'label' => __('Footer Logo', 'jointswp'),
            'description' => __('Footer Logo', 'jointswp'),
        ),
    );
}
add_filter( 'snth_custom_logos', 'snth_settings_theme_logos', 999 );

function snth_settings_media_image_sizes ( $sizes ) {
    return array(
        'thumb_1920_1080_cr' => array (
            'w'     =>  1920,
            'h'     =>  1080,
            'c'  =>  true,
            'label' =>  __('Full HD Image', 'snthwp')
        ),
        'thumb_370_200_cr' => array (
            'w'     =>  370,
            'h'     =>  200,
            'c'  =>  true,
            'label' =>  __('Loop Thumbnail', 'snthwp')
        ),
        'thumb_720_720_cr' => array (
            'w'     =>  720,
            'h'     =>  720,
            'c'  =>  true,
            'label' =>  __('Single Product', 'snthwp')
        ),
        'thumb_530_720_cr' => array (
            'w'     =>  530,
            'h'     =>  720,
            'c'  =>  true,
            'label' =>  __('Single Hookah', 'snthwp')
        ),
        'single_product_zoom' => array (
            'w'     =>  1920,
            'h'     =>  1920,
            'c'  =>  true,
            'label' =>  __('Single Product Zoom', 'snthwp')
        ),
        'single_hookah_zoom' => array (
            'w'     =>  720,
            'h'     =>  1920,
            'c'  =>  true,
            'label' =>  __('Single Hookah Zoom', 'snthwp')
        ),
        'single_post_thumb_landscape' => array (
            'w'     =>  1140,
            'h'     =>  642,
            'c'  =>  true,
            'label' =>  __('Single Post Landscape', 'snthwp')
        ),
    );
}
add_filter( 'snth_image_sizes', 'snth_settings_media_image_sizes', 999 );

function snth_settings_media_image_sizes_to_remove ( $sizes ) {
    return array(
        'post-thumbnail', 'medium'
    );
}
add_filter( 'snth_image_sizes_to_remove', 'snth_settings_media_image_sizes_to_remove', 999 );

