<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Coupon
 * @subpackage Woo_All_In_One_Coupon/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Coupon
 * @subpackage Woo_All_In_One_Coupon/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Coupon_Public {

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
	    if (apply_filters( 'wooaiocoupon_enqueue_styles', true )) {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-coupon-public.css', array(), $this->version, 'all' );
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-coupon-public.js', array( 'jquery' ), $this->version, true );

		$localize_array = array(
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'snth_nonce' )
        );

		if (defined('GRC_V3_KEY')) {
            $localize_array['grcV3Key'] = GRC_V3_KEY;
        }

        wp_localize_script( $this->plugin_name, 'wooaiocouponJsObj',  $localize_array);
	}

	public function add_shortcodes() {
        add_shortcode( 'wooaiocoupon_form', array('Woo_All_In_One_Coupon_Form', 'form_shortcode') );
    }

}
