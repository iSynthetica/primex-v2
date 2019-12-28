<?php
/**
 * Plugin Name: Woo All In One Catalogue
 * Description: Show all products on one page.
 * Version: 0.0.1
 * Author: Synthetica
 * Author URI: https://synthetica.com.ua
 * Text Domain: woo-all-in-one-catalogue
 * Domain Path: /languages/
 *
 * @package WooAIOCatalogue
 */

defined( 'ABSPATH' ) || exit;


define( 'WOOAIOCATALOGUE_VERSION', '0.0.1' );
define('WOOAIOCATALOGUE_URL', plugins_url() . '/woo-all-in-one-catalogue');
define('WOOAIOCATALOGUE_CSS_URL', WOOAIOCATALOGUE_URL . '/assets/css');
define('WOOAIOCATALOGUE_JS_URL', WOOAIOCATALOGUE_URL . '/assets/js');

if ( ! defined( 'WOOAIOCATALOGUE_FILE' ) ) {
    define( 'WOOAIOCATALOGUE_FILE', __FILE__ );
}

define( 'WOOAIOCATALOGUE_PATH', plugin_dir_path(__FILE__) );

require_once( 'includes/core.php' );
require_once( 'includes/functions.php' );
require_once( 'includes/wooaioc-template-hooks.php' );
require_once( 'includes/wooaioc-template-functions.php' );

function wooaioc_woocommerce_notice() {
    echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Woo All In One Catalogue requires Woocommerce plugin to be installed and active. You can download %s here.', 'woo-product-reviews-shrtcd' ), '<a href="https://woocommerce.com/" target="_blank">Woocommerce</a>' ) . '</strong></p></div>';
}

if ( !wooaioc_is_plugin_activated( 'woocommerce', 'woocommerce.php' ) ) {
    add_action( 'admin_notices', 'wooaioc_woocommerce_notice' );

    return;
}

require_once( 'includes/shortcodes.php' );
