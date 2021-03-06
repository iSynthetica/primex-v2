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

    /**
     * Set discount rules globally
     */
	public function set_global_discount_for_user() {
        $do_load = false;

        if (!is_admin()) {
            $do_load = true;
        }

        $do_load = apply_filters('wooaiodiscount_load_global_discount_for_user', $do_load);

        if (!$do_load) {
            return;
        }

        global $wooaiodiscount_product_rules;
        global $wooaiodiscount_user_rules;
        global $wooaiodiscount_current_user_rule;
        global $wooaiodiscount_current_discount_rule_id;

        $wooaiodiscount_product_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $wooaiodiscount_user_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $wooaiodiscount_current_user_rule = array();

        if (empty($wooaiodiscount_user_rules)) {
            return;
        }

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

                unset($base_discount);
                unset($base_discount_type);
                unset($base_discount_priority);
            }

            if (!empty($user_rule["before_discount"]["discount_id"]) && !empty($wooaiodiscount_product_rules[$user_rule["before_discount"]["discount_id"]])) {
                $before_discount = $wooaiodiscount_product_rules[$user_rule["before_discount"]["discount_id"]]['discounts'];
                $before_discount_type = $wooaiodiscount_product_rules[$user_rule["before_discount"]["discount_id"]]['type'];
                $before_discount_priority = !empty($wooaiodiscount_product_rules[$user_rule["before_discount"]["discount_id"]]['priority']) ? $wooaiodiscount_product_rules[$user_rule["before_discount"]["discount_id"]]['priority'] : 10;

                $user_rule["before_discount"]["discount"] = $before_discount;
                $user_rule["before_discount"]["type"] = $before_discount_type;
                $user_rule["before_discount"]["priority"] = $before_discount_priority;

                unset($before_discount);
                unset($before_discount_type);
                unset($before_discount_priority);
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

        $wooaiodiscount_current_discount_rule_id = !empty($wooaiodiscount_current_user_rule["base_discount"]["discount_id"]) ? $wooaiodiscount_current_user_rule["base_discount"]["discount_id"] : '';

        //md5( wp_json_encode( apply_filters( 'woocommerce_get_variation_prices_hash', $price_hash, $product, $for_display ) ) );

        set_transient('wooaiodiscount_current_user_rule_hash', md5( wp_json_encode( $wooaiodiscount_current_user_rule ) ) );
    }

    public function set_woocommerce_filters() {
        if (is_admin()) return;

        global $wooaiodiscount_current_user_rule;

        if (empty($wooaiodiscount_current_user_rule)) {
            return;
        }

        // Set base price according to current user rules
         wooaiodiscount_set_discount_rules();

        // Set Base HTML Price
        add_filter('woocommerce_get_price_html', 'wooaiodiscount_get_price_html', 1000, 2);

        if (!empty($wooaiodiscount_current_user_rule["before_discount"]["discount"])) {
            add_filter('woocommerce_get_price_html', 'wooaiodiscount_get_before_discount_price_html', 1100, 2);
        }
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
