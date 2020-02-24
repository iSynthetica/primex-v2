<?php
/**
 * Hooka Theme functions and definitions
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );

$the_theme = wp_get_theme();
$theme_version = $the_theme->get( 'Version' );
$theme_author = $the_theme->get( 'Author' );
$theme_author_url = $the_theme->get( 'AuthorURI' );

define ('SNTH_VERSION', $theme_version);
define ('SNTH_AUTHOR', $theme_author);
define ('SNTH_AUTHOR_URL', $theme_author_url);

define('SNTH_DIR', get_template_directory());
define('SNTH_ASSETS', SNTH_DIR.'/assets');
define('SNTH_STYLES', SNTH_ASSETS.'/styles');
define('SNTH_SCRIPTS', SNTH_ASSETS.'/scripts');
define('SNTH_VENDORS', SNTH_ASSETS.'/vendors');
define('SNTH_IMAGES', SNTH_ASSETS.'/images');
define('SNTH_FONTS', SNTH_ASSETS.'/fonts');
define('SNTH_INCLUDES', SNTH_DIR.'/includes');

define('SNTH_URL', get_template_directory_uri());
define('SNTH_ASSETS_URL', SNTH_URL.'/assets');
define('SNTH_STYLES_URL', SNTH_ASSETS_URL.'/styles');
define('SNTH_SCRIPTS_URL', SNTH_ASSETS_URL.'/scripts');
define('SNTH_VENDORS_URL', SNTH_ASSETS_URL.'/vendors');
define('SNTH_IMAGES_URL', SNTH_ASSETS_URL.'/images');
define('SNTH_FONTS_URL', SNTH_ASSETS_URL.'/fonts');
define('SNTH_INCLUDES_URL', SNTH_URL.'/includes');

// Helpers library
require_once(SNTH_INCLUDES.'/settings.php');

// Theme support options
require_once(SNTH_INCLUDES.'/theme-support.php');

// Remove useless WP outputs
require_once(SNTH_INCLUDES.'/cleanup.php');

// Helpers library
require_once(SNTH_INCLUDES.'/customizer.php');

// Helpers library
require_once(SNTH_INCLUDES.'/helpers.php');

// Helpers library
require_once(SNTH_INCLUDES.'/core.php');

// Custom Post Types
require_once(SNTH_INCLUDES.'/cpt.php');

// Helpers library
require_once(SNTH_INCLUDES.'/media.php');

// Helpers library
require_once(SNTH_INCLUDES.'/menu.php');

// Helpers library
require_once(SNTH_INCLUDES.'/content.php');

// Theme support options
require_once(SNTH_INCLUDES.'/enqueue-scripts.php');

// Woocommerce Core library
if (snth_is_woocommerce_active()) {
    require_once(SNTH_INCLUDES.'/wc.php');
}