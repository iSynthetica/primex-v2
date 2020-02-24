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
function joints_theme_support() {

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

    // ACF Pro Options Page
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title' => __('Theme General Settings', 'jointswp'),
            'menu_title' => __('Настройки Темы', 'jointswp'),
            'menu_slug' => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect' => false
        ));

//        acf_add_options_sub_page( array(
//            'page_title' => 'Options (' . strtoupper( 'en' ) . ')',
//            'menu_title' => __('Options (' . strtoupper( 'en' ) . ')', 'text-domain'),
//            'menu_slug'  => "options_en",
//            'post_id'    => 'options_en',
//            'parent'     => 'theme-general-settings'
//        ) );
    }
	
	// Set the maximum allowed width for any content in the theme, like oEmbeds and images added to posts.
	$GLOBALS['content_width'] = apply_filters( 'joints_theme_support', 1200 );	
	
}
add_action( 'after_setup_theme', 'joints_theme_support' );

function joints_google_map_api( $api ) {
    $api['key'] = 'AIzaSyDBeGNjLt_srVFXjDjduGyHtGu-fzn_Pt4';
    return $api;
}
add_filter('acf/fields/google_map/api', 'joints_google_map_api');