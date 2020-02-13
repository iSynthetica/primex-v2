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
        add_filter('woocommerce_before_calculate_totals', 'wooaiocurrency_before_calculate_totals', 1000, 2);
        add_filter('woocommerce_cart_product_price', 'wooaiocurrency_cart_product_price', 10, 2 );


        add_filter('woocommerce_product_get_regular_price', 'wooaiocurrency_product_get_regular_price', 1000, 2 );
        add_filter('woocommerce_product_get_sale_price', 'wooaiocurrency_product_get_sale_price', 1000, 2 );
        add_filter('woocommerce_product_get_price', 'wooaiocurrency_product_get_price', 1000, 2 );
        add_filter('woocommerce_product_variation_get_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
        add_filter('woocommerce_product_variation_get_regular_price', 'wooaiocurrency_product_variation_get_regular_price', 1000, 2 );
        add_filter('woocommerce_product_variation_get_sale_price', 'wooaiocurrency_product_variation_get_sale_price', 1000, 2 );
        add_filter('woocommerce_variation_prices', 'wooaiocurrency_variation_prices', 1000 );


        add_action( 'woocommerce_cart_loaded_from_session', 'wooaiocurrency_before_mini_cart', 1000 );

//        add_filter('woocommerce_before_calculate_totals', 'wooaiocurrency_set_currency_symbol', 100);
//        add_filter('woocommerce_after_calculate_totals', 'wooaiocurrency_reset_currency_symbol', 1000);

//        add_action('woocommerce_before_cart_contents', 'wooaiocurrency_set_currency_symbol', 1000);
//        add_action('woocommerce_after_cart_contents', 'wooaiocurrency_reset_currency_symbol', 1000);
//
//        add_action('woocommerce_before_mini_cart', 'wooaiocurrency_set_currency_symbol', 1000);
//        add_action('woocommerce_after_mini_cart', 'wooaiocurrency_reset_currency_symbol', 1000);

    }

    public function set_global_currency_rule() {
        if (is_admin()) {
            return;
        }

        global $wooaiocurrency_rules;

        $wooaiocurrency_rules = array();

        $base_currency = get_option( 'woocommerce_currency' );

        $currency_rules = Woo_All_In_One_Currency_Rules::get_all();

        foreach ($currency_rules as $currency_code => $currency_rule) {
            if ($currency_code !== $base_currency && empty($currency_rule['rates'])) {
                unset($currency_rules[$currency_code]);
            }
        }

        if (count($currency_rules) < 2) {
            $wooaiocurrency_rules['current_currency_code'] = $base_currency;
            $wooaiocurrency_rules['current_currency_rule'] = $currency_rules[$base_currency];
            $wooaiocurrency_rules['switcher'] = array();
        } else {
            if (!empty($_COOKIE['wooaiocurrency'])) {
                $current_currency = $_COOKIE['wooaiocurrency'];

                if (empty($currency_rules[$current_currency])) {
                    $current_currency = false;

                    setcookie('wooaiocurrency_update_minicart', 1, time() + (86400 * 360), '/');
                    unset($_COOKIE['wooaiocurrency']);
                    $res = setcookie('wooaiocurrency', '', time() - 3600);
                }
            }

            if (empty($current_currency)) {
                foreach ($currency_rules as $currency_code => $currency_rule) {
                    if (!empty($currency_rule['main'])) {
                        $current_currency =  $currency_code;
                    }
                }
            }

            if (empty($current_currency)) {
                $current_currency =  $base_currency;
            }

            $wooaiocurrency_rules['current_currency_code'] = $current_currency;
            $wooaiocurrency_rules['current_currency_rule'] = $currency_rules[$current_currency];
            $switcher_rules = $currency_rules;

            unset($switcher_rules[$current_currency]);

            $wooaiocurrency_rules['switcher'] = $switcher_rules;
        }
    }
}
