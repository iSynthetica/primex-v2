<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Currency_Public {

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
        if (apply_filters( 'wooaiocurrency_enqueue_styles', true )) {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-currency-public.css', array(), $this->version, 'all' );
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-cookie', plugin_dir_url( __FILE__ ) . 'js/jquery.cookie.js', array( 'jquery' ), '1.4.1', true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-currency-public.js', array( 'jquery' ), $this->version, true );
	}

	public function set_woocommerce_filters() {
        add_filter('woocommerce_currency', 'wooaiocurrency_currency', 1000, 2);
        wooaiocurrency_set_currency_rules();
        add_action( 'woocommerce_cart_loaded_from_session', 'wooaiocurrency_before_mini_cart', 1000 );
    }

    public function set_global_currency_rule() {
        wooaiocurrency_set_global_currency_rule();
    }
}
