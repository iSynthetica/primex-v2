<?php
/**
 * Remove useless WP outputs
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fire all our initial functions at the start
 */
function snth_start() {

    // launching operation cleanup
    add_action( 'init', 'snth_head_cleanup' );

    // remove pesky injected css for recent comments widget
    add_filter( 'wp_head', 'snth_remove_wp_widget_recent_comments_style', 1 );

    // clean up comment styles in the head
    add_action( 'wp_head', 'snth_remove_recent_comments_style', 1 );

    // clean up gallery output in wp
    add_filter( 'gallery_style', 'snth_gallery_style' );

    // cleaning up excerpt
    add_filter( 'excerpt_more', 'snth_excerpt_more' );
}
add_action('after_setup_theme','snth_start', 16);

/**
 * The default wordpress head is a mess.
 *
 * Let's clean it up by removing all the junk we don't need.
 */
function snth_head_cleanup() {
	// Remove category feeds
	// remove_action( 'wp_head', 'feed_links_extra', 3 );

	// Remove post and comment feeds
	// remove_action( 'wp_head', 'feed_links', 2 );

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
}

/**
 * Remove injected CSS for recent comments widget
 */
function snth_remove_wp_widget_recent_comments_style() {
   if ( has_filter('wp_head', 'wp_widget_recent_comments_style') ) {
      remove_filter('wp_head', 'wp_widget_recent_comments_style' );
   }
}

/**
 * Remove injected CSS from recent comments widget
 */
function snth_remove_recent_comments_style() {
  global $wp_widget_factory;

  if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
  }
}

/**
 * Remove injected CSS from gallery
 *
 * @param $css
 *
 * @return null|string|string[]
 */
function snth_gallery_style($css) {
  return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}

/**
 * This removes the annoying […] to a Read More link
 *
 * @param $more
 *
 * @return string
 */
function snth_excerpt_more($more) {
	global $post;

	return '';

    return '<a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="'. __('Read', 'snthwp') . get_the_title($post->ID).'">'. __('... Read more &raquo;', 'snthwp') .'</a>';
}

//
/**
 * Stop WordPress from using the sticky class
 * (which conflicts with Foundation),
 * and style WordPress sticky posts using the .wp-sticky class instead
 *
 * @param $classes
 *
 * @return array
 */
function remove_sticky_class($classes)
{
	if (in_array('sticky', $classes)) {
		$classes = array_diff($classes, array("sticky"));
		$classes[] = 'wp-sticky';
	}

	return $classes;
}
add_filter('post_class','remove_sticky_class');

/**
 * This is a modified the_author_posts_link() which just returns the link.
 * This is necessary to allow usage of the usual l10n process with printf()
 * @return bool|string
 */
function snth_get_the_author_posts_link()
{
	global $authordata;

	if ( !is_object( $authordata ) )
		return false;

	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
		get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
		esc_attr( sprintf( __( 'Posts by %s', 'snthwp' ), get_the_author() ) ), // No further l10n needed, core will take care of this one
		get_the_author()
	);

	return $link;
}

/**
 * Disable the emoji's
 */
function snth_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'tiny_mce_plugins', 'snth_disable_emojis_tinymce' );
    add_filter( 'wp_resource_hints', 'snth_disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'snth_disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param array $plugins
 * @return array Difference betwen the two arrays
 */
function snth_disable_emojis_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function snth_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
    if ( 'dns-prefetch' == $relation_type ) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

        $urls = array_diff( $urls, array( $emoji_svg_url ) );
    }

    return $urls;
}