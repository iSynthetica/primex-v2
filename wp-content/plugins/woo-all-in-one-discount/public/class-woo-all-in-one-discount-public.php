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
        if (is_admin()) {
            return;
        }
        global $wooaiodiscount_product_rules;
        global $wooaiodiscount_user_rules;
        global $wooaiodiscount_current_user_rule;

        $wooaiodiscount_product_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $wooaiodiscount_user_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $wooaiodiscount_current_user_rule = array();

        $user = null;
        $user_roles = array('all_users');

        if (is_user_logged_in()) {
            $user_roles[] = 'registered_users';
            $user = wp_get_current_user();

            if (property_exists($user, 'roles')) {
                $user_roles = array_merge($user_roles, $user->roles);
            }
        } else {
            $user_roles[] = 'unregistered_users';
        }

        $all_users_rule_priority = 0;
        $all_users_rule = array();
        $unregistered_users_priority = 0;
        $unregistered_users_rule = array();
        $registered_users_priority = 0;
        $registered_users_rule = array();
        $role_users_rule_priority = 0;
        $role_users_rule = array();

        foreach ($wooaiodiscount_user_rules as $user_rule_id => $user_rule) {
            if (!empty($user_rule["base_discount"]["discount_id"]) && !empty($wooaiodiscount_product_rules[$user_rule["base_discount"]["discount_id"]])) {
                $base_discount = $wooaiodiscount_product_rules[$user_rule["base_discount"]["discount_id"]]['discounts'];
                $base_discount_type = $wooaiodiscount_product_rules[$user_rule["base_discount"]["discount_id"]]['type'];
                $base_discount_priority = !empty($wooaiodiscount_product_rules[$user_rule["base_discount"]["discount_id"]]['priority']) ? $wooaiodiscount_product_rules[$user_rule["base_discount"]["discount_id"]]['priority'] : 10;
                $user_rule["base_discount"]["discount"] = $base_discount;
                $user_rule["base_discount"]["type"] = $base_discount_type;
                $user_rule["base_discount"]["priority"] = $base_discount_priority;
            }

            $wooaiodiscount_user_rules[$user_rule_id] = $user_rule;

            if ($user_rule['type'] === 'all_users') {
                if ((int)$user_rule['priority'] > $all_users_rule_priority) {
                    $all_users_rule = $user_rule;
                    $all_users_rule_priority = (int)$user_rule['priority'];
                }
            } elseif ($user_rule['type'] === 'unregistered_users') {
                if ((int)$user_rule['priority'] > $unregistered_users_priority) {
                    $unregistered_users_rule = $user_rule;
                    $unregistered_users_priority = (int)$user_rule['priority'];
                }
            } elseif ($user_rule['type'] === 'registered_users') {
                if ((int)$user_rule['priority'] > $registered_users_priority) {
                    $registered_users_rule = $user_rule;
                    $registered_users_priority = (int)$user_rule['priority'];
                }
            } elseif ($user_rule['type'] === 'user_roles') {
                if (!empty($user_rule['role']) && in_array($user_rule['role'], $user_roles)) {
                    if ((int)$user_rule['priority'] > $role_users_rule_priority) {
                        $role_users_rule = $user_rule;
                        $role_users_rule_priority = (int)$user_rule['priority'];
                    }
                }
            }
        }

        if (!empty($all_users_rule)) {
            $wooaiodiscount_current_user_rule = $all_users_rule;
        }

        if (!$user) {
            if (!empty($unregistered_users_rule)) {
                $wooaiodiscount_current_user_rule = $unregistered_users_rule;
            }
        } else {
            if (!empty($registered_users_rule)) {
                $wooaiodiscount_current_user_rule = $registered_users_rule;
            }

            if (!empty($role_users_rule)) {
                $wooaiodiscount_current_user_rule = $role_users_rule;
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

        global $wooaiodiscount_current_user_rule;

        if (empty($wooaiodiscount_current_user_rule)) {
            return $price;
        }

        $before_discount_price = '';
        $before_base_price = '';

        if (!empty($wooaiodiscount_current_user_rule["base_discount"]["discount_label"])) {
            $before_discount_price = '<b class="woocommerce-Price-label label">' . $wooaiodiscount_current_user_rule["base_discount"]["discount_label"] . '</b> ';
        }

        if (!empty($wooaiodiscount_current_user_rule["base_discount"]["price_label"])) {
            $before_base_price = '<b class="woocommerce-Price-label label">' . $wooaiodiscount_current_user_rule["base_discount"]["price_label"] . '</b> ';
        }

        $product_type = $product->get_type();

	    if ('simple' === $product_type || 'variation' === $product_type) {
            //wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-product.php:1758
            $product_base_price = $product->get_price();

            if ( '' === $product_base_price ) {
                $price = apply_filters( 'woocommerce_empty_price_html', '', $product );
            } else {
                $product_rule_price = Woo_All_In_One_Discount_Rules::get_price($product_base_price, $product);


                if ( $product->is_on_sale() ) {
                    $product_regular_price = $product->get_regular_price();


                    $price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product_regular_price ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
                } else {

                    if ((int)$product_base_price === (int)$product_rule_price) {
                        return $price;
                    }

                    $price = '';

                    $discount_product_price = $product_base_price;
                    $product_price = $product_rule_price;

                    $price .= $before_discount_price . wc_price( $product_price ) . $product->get_price_suffix();

                    if (!empty($before_base_price)) {
                        $price .= '<br>';
                        $price .= '<small>' . $before_base_price . wc_price( $discount_product_price ) . $product->get_price_suffix() . '</small>';
                    }

                    if (!empty($wooaiodiscount_current_user_rule["base_discount"]["discount_amount_label"])) {
                        $discount_amount_label = $wooaiodiscount_current_user_rule["base_discount"]["discount_amount_label"];
                        $price .= '<br><small><b class="woocommerce-Price-label label">' . $discount_amount_label . '</b> '. Woo_All_In_One_Discount_Rules::get_discount_amount($product) .'%</small> ';
                    }
                }
            }
        } elseif ('variable' === $product_type) {
            $prices = $product->get_variation_prices( true );

            if ( empty( $prices['price'] ) ) {
                $price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
            } else {
                $min_base_price     = current( $prices['price'] );
                $max_base_price     = end( $prices['price'] );
                $min_reg_base_price = current( $prices['regular_price'] );
                $max_reg_base_price = end( $prices['regular_price'] );

                $min_base_rule_price     = Woo_All_In_One_Discount_Rules::get_price($min_base_price, $product);
                $max_base_rule_price     = Woo_All_In_One_Discount_Rules::get_price($max_base_price, $product);
                $min_reg_base_rule_price = Woo_All_In_One_Discount_Rules::get_price($min_reg_base_price, $product);
                $max_reg_base_rule_price = Woo_All_In_One_Discount_Rules::get_price($max_reg_base_price, $product);

                if ($min_base_rule_price !== $min_base_price || $max_base_rule_price !== $max_base_price) {
                    if ( $min_base_rule_price !== $max_base_rule_price ) {
                        $price = $before_discount_price . wc_format_price_range( $min_base_rule_price, $max_base_rule_price );

                        if (!empty($before_base_price)) {
                            $price .= '<br>';
                            $price .= '<small>' . $before_base_price . wc_format_price_range( $min_base_price, $max_base_price ) . '</small>';
                        }
                    } elseif ( $product->is_on_sale() && $min_reg_base_price === $max_reg_base_price ) {
                        $price = $before_discount_price . wc_format_sale_price( wc_price( $max_reg_base_price ), wc_price( $min_base_price ) );
                    } else {
                        $price = $before_discount_price . wc_price( $min_base_price );
                    }
                } else {
                    if ( $min_base_price !== $max_base_price ) {
                        $price = wc_format_price_range( $min_base_price, $max_base_price );
                    } elseif ( $product->is_on_sale() && $min_reg_base_price === $max_reg_base_price ) {
                        $price = wc_format_sale_price( wc_price( $max_reg_base_price ), wc_price( $min_base_price ) );
                    } else {
                        $price = wc_price( $min_base_price );
                    }
                }


                $price = $price . $product->get_price_suffix();
            }

            return $price;
        } elseif ('grouped' === $product_type) {
            return $price;
        }

	    return $price;
    }

    public function recalculate_totals($cart_object) {
        if (is_admin()) {
            return;
        }
        foreach ( $cart_object->get_cart() as $hash => $value ) {
            $price = $value['data']->get_price();
            $product_id = $value['data']->get_id();
            $product = wc_get_product($product_id);

            $value['data']->set_price(Woo_All_In_One_Discount_Rules::get_price($price, $product));
        }
    }
}
