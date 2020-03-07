<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Turbosms
 * @subpackage Woo_All_In_One_Turbosms/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Turbosms
 * @subpackage Woo_All_In_One_Turbosms/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Turbosms_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-turbosms-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-turbosms-public.js', array( 'jquery' ), $this->version, false );

	}

	public function woocommerce_events() {
		$turbosms_settings = Woo_All_In_One_Turbosms_Helpers::get_settings();

		if (!empty($turbosms_settings['login']) && !empty($turbosms_settings['password'])) {
			add_action( 'woocommerce_checkout_order_processed', 'wooaio_turbosms_order_created', 10, 3 );
			add_action('woocommerce_order_status_changed', 'wooaio_turbosms_order_status_changed', 50, 4);
		}
	}
}
