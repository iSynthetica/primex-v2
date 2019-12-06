<?php
/**
 * Plugin Name:     Woo Product Reviews Shortcode
 * Description:     Add your product reviews on any page using shortcode with product ID
 * Version:         0.0.1
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     woo-product-reviews-shrtcd
 * Domain Path:     /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPRSHRTCD_VERSION', '0.0.1' );

if ( ! defined( 'WPRSHRTCD_FILE' ) ) {
    define( 'WPRSHRTCD_FILE', __FILE__ );
}

require_once( 'includes/functions.php' );

add_action('admin_menu', 'wprshrtcd_plugin_menu');

function wprshrtcd_plugin_menu() {
    add_options_page(__('Woo Product Reviews Shortcode Help', 'woo-product-reviews-shrtcd'), __('Woo Reviews Shortcode', 'woo-product-reviews-shrtcd'), 'manage_options', 'wprshrtcd-help', 'wprshrtcd_plugin_menu_show');
}

function wprshrtcd_plugin_menu_show() {
    include('admin/help.php');
}

add_filter('plugin_action_links_' . plugin_basename(dirname(__FILE__)) . '/woo-product-reviews-shrtcd.php', 'wprshrtcd_add_plugin_screen_link');

function wprshrtcd_woocommerce_notice() {
    /* translators: 1. URL link. */
    echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Woo Product Reviews Shortcode requires Woocommerce plugin to be installed and active. You can download %s here.', 'woo-product-reviews-shrtcd' ), '<a href="https://woocommerce.com/" target="_blank">Woocommerce</a>' ) . '</strong></p></div>';
}

if ( !wprshrtcd_is_plugin_activated( 'woocommerce', 'woocommerce.php' ) ) {
        add_action( 'admin_notices', 'wprshrtcd_woocommerce_notice' );

    return;
}

require_once( 'includes/shortcodes.php' );

add_action( 'plugins_loaded', 'wprshrtcd_load_plugin_textdomain' );

function wprshrtcd_load_plugin_textdomain() {
    add_filter( 'plugin_locale', 'wprshrtcd_check_de_locale');

    load_plugin_textdomain(
        'woo-product-reviews-shrtcd',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );

    remove_filter( 'plugin_locale', 'wprshrtcd_check_de_locale');
}

function wprshrtcd_check_de_locale($domain) {
    $site_lang = get_user_locale();
    $de_lang_list = array(
        'de_CH_informal',
        'de_DE_formal',
        'de_AT',
        'de_CH',
        'de_DE'
    );

    if (in_array($site_lang, $de_lang_list)) return 'de_DE';
    return $domain;
}
