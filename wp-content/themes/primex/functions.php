<?php
/**
 * Functions
 *
 * @package WordPress
 * @subpackage Prime-X
 * @since Prime-X 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

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

// Settings
require_once(SNTH_INCLUDES.'/settings.php');

require_once(SNTH_INCLUDES.'/clean.php');
// Helpers library
require_once(SNTH_INCLUDES.'/helpers.php');
// CPT library
require_once(SNTH_INCLUDES.'/cpt.php');
// Theme support options
require_once(SNTH_INCLUDES.'/enqueue-scripts.php');
// Theme support options
require_once(SNTH_INCLUDES.'/theme-support.php');
// Theme support options
require_once(SNTH_INCLUDES.'/shortcodes.php');
// Comments
require_once(SNTH_INCLUDES.'/comments.php');
// Sidebar
require_once(SNTH_INCLUDES.'/sidebar.php');
// Menues
require_once(SNTH_INCLUDES.'/menu.php');
// Templates
require_once(SNTH_INCLUDES.'/content-templates.php');
// Templates
require_once(SNTH_INCLUDES.'/wc.php');

if (is_admin()) {
    require_once(SNTH_INCLUDES.'/admin.php');
}


// Templates
if (file_exists(SNTH_INCLUDES.'/ajax-search.php')) {
    require_once(SNTH_INCLUDES.'/ajax-search.php');
}

add_filter('berocket_aapf_is_filtered_page_check', 'snth_is_filtered_page_check', 100, 1);

function snth_is_filtered_page_check($filtered) {
    if (!empty($_GET['s'])) {
        if (!empty($_GET['dgwt_wcas'])) {
            $filtered = false;
        } elseif (!empty($_GET["wc-ajax"]) && $_GET["wc-ajax"] == 'dgwt_wcas_ajax_search') {
            $filtered = false;
        }
    }

//    if( ! empty($_GET['s']) && (!empty($_GET['dgwt_wcas'])) || (!empty($_GET["wc-ajax"]) && $_GET["wc-ajax"] == 'dgwt_wcas_ajax_search')) {
//        $filtered = false;
//    }

    return $filtered;
}

function jmworld_woo_betanet_epost_free($rates, $package) {
    $rate_free_ids = array('novaposhta_courier:15', 'betanet_epost7');
    $cart_total_free = 100;
    $cart_total = (int) WC()->cart->get_cart_contents_total();

    if ($cart_total < $cart_total_free) {
        return $rates;
    }

    foreach ($rates as $rate_id => $rate_data) {
        if (in_array($rate_id, $rate_free_ids)) {
            $rate_data->set_cost(0);

            $rates[$rate_id] = $rate_data;
        }
    }

    return $rates;
}

add_filter('woocommerce_package_rates', 'jmworld_woo_betanet_epost_free', 100, 2);