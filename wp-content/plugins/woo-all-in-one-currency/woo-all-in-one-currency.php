<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://synthetica.com.ua
 * @since             1.0.0
 * @package           Woo_All_In_One_Currency
 *
 * @wordpress-plugin
 * Plugin Name:       Woo All In One Multi Currency
 * Plugin URI:        http://synthetica.com.ua
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Synthetica
 * Author URI:        http://synthetica.com.ua
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-all-in-one-currency
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define( 'WOO_ALL_IN_ONE_CURRENCY_VERSION', '1.0.0' );
define( 'WOO_ALL_IN_ONE_CURRENCY_FILE', __FILE__ );
define( 'WOO_ALL_IN_ONE_CURRENCY_PATH', plugin_dir_path(__FILE__) );
define( 'WOO_ALL_IN_ONE_CURRENCY_INC', WOO_ALL_IN_ONE_CURRENCY_PATH . 'includes' );
define( 'WOO_ALL_IN_ONE_CURRENCY_PUBLIC', WOO_ALL_IN_ONE_CURRENCY_PATH . 'public' );
define( 'WOO_ALL_IN_ONE_CURRENCY_ADMIN', WOO_ALL_IN_ONE_CURRENCY_PATH . 'admin' );

//define('WOO_ALL_IN_ONE_CURRENCY_URL', plugins_url() . '/woo-all-in-one-currency');
//define('WOO_ALL_IN_ONE_CURRENCY_CSS_URL', WOO_ALL_IN_ONE_CURRENCY_URL . '/public/css');
//define('WOO_ALL_IN_ONE_CURRENCY_JS_URL', WOO_ALL_IN_ONE_CURRENCY_URL . '/public/js');
//define('WOO_ALL_IN_ONE_CURRENCY_ADMIN_CSS_URL', WOO_ALL_IN_ONE_CURRENCY_URL . '/admin/css');
//define('WOO_ALL_IN_ONE_CURRENCY_ADMIN_JS_URL', WOO_ALL_IN_ONE_CURRENCY_URL . '/admin/js');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-all-in-one-currency-activator.php
 */
function activate_woo_all_in_one_currency() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-all-in-one-currency-activator.php';
	Woo_All_In_One_Currency_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-all-in-one-currency-deactivator.php
 */
function deactivate_woo_all_in_one_currency() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-all-in-one-currency-deactivator.php';
	Woo_All_In_One_Currency_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_all_in_one_currency' );
register_deactivation_hook( __FILE__, 'deactivate_woo_all_in_one_currency' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-all-in-one-currency.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_all_in_one_currency() {

	$plugin = new Woo_All_In_One_Currency();
	$plugin->run();

}
run_woo_all_in_one_currency();
