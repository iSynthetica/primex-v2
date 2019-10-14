<?php
/**
 * Setup theme
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adding WP Functions & Theme Support
 */
function snth_theme_support() {

	// Add WP Thumbnail Support
	add_theme_support( 'post-thumbnails' );
	
	// Default thumbnail size
	set_post_thumbnail_size(125, 125, true);

	// Add RSS Support
	add_theme_support( 'automatic-feed-links' );
	
	// Add Support for WP Controlled Title Tag
	add_theme_support( 'title-tag' );
	
	// Add HTML5 Support
	add_theme_support( 'html5', 
         array(
            'comment-list',
            'comment-form',
            'search-form',
         )
	);
	
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => array( 'site-title', 'site-description' ),
	) );
	
	// Adding post format support
	 add_theme_support( 'post-formats',
		array(
			'aside',             // title less blurb
			'gallery',           // gallery of images
			'link',              // quick link to other site
			'image',             // an image
			'quote',             // a quick quote
			'status',            // a Facebook like status update
			'video',             // video
			'audio',             // audio
			'chat'               // chat transcript
		)
	);
	
	// Set the maximum allowed width for any content in the theme, like oEmbeds and images added to posts.
	$GLOBALS['content_width'] = apply_filters( 'snth_theme_support', 1200 );

    // ACF Pro Options Page
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title' => __('Theme General Settings', 'snthwp'),
            'menu_title' => __('Theme settings', 'snthwp'),
            'menu_slug' => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect' => false
        ));
    }
	
}
add_action( 'after_setup_theme', 'snth_theme_support' );


function load_translations(){
    load_theme_textdomain( 'snthwp', get_template_directory() .'/languages' );
}
add_action('after_setup_theme', 'load_translations');

function snth_google_map_api( $api ) {
    $api_key = apply_filters( 'snth_gmap_api_key', '' );
    $api['key'] = $api_key;

    return $api;
}
add_filter('acf/fields/google_map/api', 'snth_google_map_api');

add_filter( 'widget_text', 'do_shortcode' );
