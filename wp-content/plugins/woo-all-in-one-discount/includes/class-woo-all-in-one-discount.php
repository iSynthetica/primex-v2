<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Discount
 * @subpackage Woo_All_In_One_Discount/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_All_In_One_Discount
 * @subpackage Woo_All_In_One_Discount/includes
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Discount {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_All_In_One_Discount_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOO_ALL_IN_ONE_DISCOUNT_VERSION' ) ) {
			$this->version = WOO_ALL_IN_ONE_DISCOUNT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-all-in-one-discount';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_All_In_One_Discount_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_All_In_One_Discount_i18n. Defines internationalization functionality.
	 * - Woo_All_In_One_Discount_Admin. Defines all hooks for the admin area.
	 * - Woo_All_In_One_Discount_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

        /**
         * Include libs classes
         */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Woo_All_In_One_Discount_Helpers.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Woo_All_In_One_Discount_Rules.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template-functions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/woo-functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-all-in-one-discount-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-all-in-one-discount-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-all-in-one-discount-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-all-in-one-discount-admin-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-all-in-one-discount-public.php';

		$this->loader = new Woo_All_In_One_Discount_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_All_In_One_Discount_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_All_In_One_Discount_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woo_All_In_One_Discount_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_admin_ajax = new Woo_All_In_One_Discount_Admin_Ajax( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu', 40 );

        $this->loader->add_action( 'wp_ajax_wooaiodiscount_create_product_discount_rule', $plugin_admin_ajax, 'create_product_discount_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_update_product_discount_rule', $plugin_admin_ajax, 'update_product_discount_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_delete_product_discount_rule', $plugin_admin_ajax, 'delete_product_discount_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_add_discount_amount_item', $plugin_admin_ajax, 'add_discount_amount_item' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_create_discount_amount_item', $plugin_admin_ajax, 'create_discount_amount_item' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_delete_discount_amount_item', $plugin_admin_ajax, 'delete_discount_amount_item' );

        $this->loader->add_action( 'wp_ajax_wooaiodiscount_create_user_discount_rule', $plugin_admin_ajax, 'create_user_discount_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_update_user_discount_rule', $plugin_admin_ajax, 'update_user_discount_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_delete_user_discount_rule', $plugin_admin_ajax, 'delete_user_discount_rule' );

        $this->loader->add_action( 'wp_ajax_wooaiodiscount_add_discount_currency_rate', $plugin_admin_ajax, 'add_discount_currency_rate' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_create_discount_currency_rate', $plugin_admin_ajax, 'create_discount_currency_rate' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_copy_discount_currency_rate', $plugin_admin_ajax, 'copy_discount_currency_rate' );
        $this->loader->add_action( 'wp_ajax_wooaiodiscount_delete_discount_currency_rate', $plugin_admin_ajax, 'delete_discount_currency_rate' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_All_In_One_Discount_Public( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'init', $plugin_public, 'set_global_discount_for_user', 10 );
		// $this->loader->add_action( 'wp_loaded', $plugin_public, 'set_global_discount_for_user', 10 );
		$this->loader->add_action( 'woocommerce_loaded', $plugin_public, 'set_global_discount_for_user', 10 );
		$this->loader->add_action( 'init', $plugin_public, 'set_woocommerce_filters', 15 );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

//		$this->loader->add_filter( 'woocommerce_before_calculate_totals', $plugin_public, 'recalculate_totals', 1000 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_All_In_One_Discount_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
