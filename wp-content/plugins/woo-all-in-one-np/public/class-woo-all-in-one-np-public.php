<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Np
 * @subpackage Woo_All_In_One_Np/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Np
 * @subpackage Woo_All_In_One_Np/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Np_Public {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-np-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-np-public.js', array( 'jquery' ), $this->version, true );
	}

	public function add_novaposhta_np_gateway($methods) {
        $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $methods );

        if (is_admin()) {
            $methods[] = 'WC_novaposhta_np_Payu';
        }

        elseif (WC()->cart->needs_shipping()){
            $methods[] = 'WC_novaposhta_np_Payu';
        }

        return $methods;
    }

    public function add_novaposhta_sm($methods) {
        return $methods;
    }

    public function init() {
        add_action('woocommerce_shipping_init', 'woionp_sm_init');
        add_action('woocommerce_shipping_init', 'woionp_sm_settings_init');
    }

}
