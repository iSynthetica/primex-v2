<?php
add_action('after_setup_theme','snthbs_start', 16);

function snthbs_start() {

    // launching operation cleanup
    add_action('init', 'snthbs_head_cleanup');

//    // remove pesky injected css for recent comments widget
//    add_filter( 'wp_head', 'joints_remove_wp_widget_recent_comments_style', 1 );
//
//    // clean up comment styles in the head
//    add_action('wp_head', 'joints_remove_recent_comments_style', 1);
//
//    // clean up gallery output in wp
//    add_filter('gallery_style', 'joints_gallery_style');
//
//    // cleaning up excerpt
//    add_filter('excerpt_more', 'joints_excerpt_more');

}

function snthbs_head_cleanup() {
    // Remove category feeds
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    // Remove post and comment feeds
    remove_action( 'wp_head', 'feed_links', 2 );
    // Remove EditURI link
    remove_action( 'wp_head', 'rsd_link' );
    // Remove Windows live writer
    remove_action( 'wp_head', 'wlwmanifest_link' );
    // Remove index link
    remove_action( 'wp_head', 'index_rel_link' );
    // Remove previous link
    remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
    // Remove start link
    remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
    // Remove links for adjacent posts
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
    // Remove WP version
    remove_action( 'wp_head', 'wp_generator' );

    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

}