<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/includes
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Currency_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woo-all-in-one-currency',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
