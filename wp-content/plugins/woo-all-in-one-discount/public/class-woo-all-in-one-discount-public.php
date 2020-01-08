<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Discount
 * @subpackage Woo_All_In_One_Discount/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Discount
 * @subpackage Woo_All_In_One_Discount/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Discount_Public {

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

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_All_In_One_Discount_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_All_In_One_Discount_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-discount-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_All_In_One_Discount_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_All_In_One_Discount_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-discount-public.js', array( 'jquery' ), $this->version, false );

	}

	public function raw_woocommerce_price($price, $product) {
	    return $price;
    }

    public function woocommerce_get_price_html($price, $product) {
	    if (is_admin()) {
            return $price;
        }
	    $product_type = $product->get_type();
	    $product_price = $product->get_price();
	    $discount_product_price = $product_price * 0.80;

	    if ('simple' === $product_type) {
            $price = '';

            $price .= wc_price( $product_price ) . $product->get_price_suffix();
            $price .= '<br>';
            $price .= '<small><b class="woocommerce-Price-label label">' . __('Wholesale price', 'woo-all-in-one-discount') . ':</b> ' . wc_price( $discount_product_price ) . $product->get_price_suffix() . '</small>';
        }

	    return $price;
    }

}
