<?php
/**
 * Plugin Name: Woo All In One Import Export
 * Description: Import export woocommerce shop.
 * Version: 0.0.1
 * Author: Synthetica
 * Author URI: https://synthetica.com.ua
 * Text Domain: woo-all-in-one-ie
 * Domain Path: /languages/
 *
 * @package WooAIOImportExport
 */

defined( 'ABSPATH' ) || exit;

define('WOOAIOIE_VERSION', '0.0.1');
define('WOOAIOIE_URL', plugins_url() . '/woo-aio-import-export');
define('WOOAIOIE_CSS_URL', WOOAIOIE_URL . '/assets/css');
define('WOOAIOIE_FILE', __FILE__ );
define('WOOAIOIE_PATH', plugin_dir_path(__FILE__));

require_once(WOOAIOIE_PATH . '/includes/functions.php');
require_once(WOOAIOIE_PATH . '/includes/admin.php');
