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

	public function set_global_discount_for_user() {
        global $wooaiodiscount_product_rules;
        global $wooaiodiscount_user_rules;
        global $wooaiodiscount_current_user_rule;

        $wooaiodiscount_product_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $wooaiodiscount_user_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();

        $user = null;

        if (is_user_logged_in()) {
            $user = wp_get_current_user();
        }

        if (!$user) {
            $all_users_rule = false;
            $unregistered_user_rule = false;

            foreach ($wooaiodiscount_user_rules as $user_rule_id => $user_rule) {
                $user_rule['id'] = $user_rule_id;
                $extra_charge = '';
                $base_discount = '';

                if (!empty($user_rule["extra_charge"]["extra_charge"]) && !empty($wooaiodiscount_product_rules[$user_rule["extra_charge"]["extra_charge"]])) {
                    $extra_charge = $wooaiodiscount_product_rules[$user_rule["extra_charge"]["extra_charge"]]['discounts'];
                }

                if (!empty($user_rule["base_discount"]["base_discount"]) && !empty($wooaiodiscount_product_rules[$user_rule["base_discount"]["base_discount"]])) {
                    $base_discount = $wooaiodiscount_product_rules[$user_rule["base_discount"]["base_discount"]]['discounts'];
                }

                $user_rule['extra_charge']["extra_charge"] = $extra_charge;
                $user_rule['base_discount']["base_discount"] = $base_discount;

                if ($user_rule['type'] === 'unregistered_users') {
                    $unregistered_user_rule = $user_rule;
                } elseif ($user_rule['type'] === 'all_users') {
                    $all_users_rule = $user_rule;
                }
            }

            if (!empty($all_users_rule)) {
                $wooaiodiscount_current_user_rule = $all_users_rule;
            }

            if (!empty($unregistered_user_rule)) {
                $wooaiodiscount_current_user_rule = $unregistered_user_rule;
            }
        }
    }

	public function raw_woocommerce_price($price, $product) {
	    return $price;
    }

    public function woocommerce_get_price_html($price, $product) {
        if (is_admin()) {
            return $price;
        }

        global $wooaiodiscount_product_rules;
        global $wooaiodiscount_user_rules;
        global $wooaiodiscount_current_user_rule;

	    $product_type = $product->get_type();
	    $product_price = $product->get_price();

	    if ('simple' === $product_type) {
            $extra_charge_percent = 0;

	        if (!empty($wooaiodiscount_current_user_rule["extra_charge"]["extra_charge"])) {
	            $all_products_extra = 0;

	            foreach ($wooaiodiscount_current_user_rule["extra_charge"]["extra_charge"] as $extra_charge) {
	                if ($extra_charge['apply'] === 'all_products' && $all_products_extra < (int)$extra_charge['amount']) {
                        $all_products_extra = (int)$extra_charge['amount'];
                    }
                }

	            $extra_charge_percent = $all_products_extra;
            }
            $price = '';

            $discount_product_price = $product_price;
            $product_price = $product_price + ($product_price * ($extra_charge_percent / 100));

            $price .= wc_price( $product_price ) . $product->get_price_suffix();
            $price .= '<br>';
            $price .= '<small><b class="woocommerce-Price-label label">' . __('Wholesale price', 'woo-all-in-one-discount') . ':</b> ' . wc_price( $discount_product_price ) . $product->get_price_suffix() . '</small>';
        }

	    return $price;
    }

}
