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
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/includes
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
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/includes
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Currency {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_All_In_One_Currency_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'WOO_ALL_IN_ONE_CURRENCY_VERSION' ) ) {
			$this->version = WOO_ALL_IN_ONE_CURRENCY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-all-in-one-currency';

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
	 * - Woo_All_In_One_Currency_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_All_In_One_Currency_i18n. Defines internationalization functionality.
	 * - Woo_All_In_One_Currency_Admin. Defines all hooks for the admin area.
	 * - Woo_All_In_One_Currency_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Woo_All_In_One_Currency_Helpers.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Woo_All_In_One_Currency_Rules.php';

		if (!function_exists('run_woo_all_in_one')) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wooaio-functions.php';
        }

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template-functions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wc-functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-all-in-one-currency-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-all-in-one-currency-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-all-in-one-currency-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-all-in-one-currency-admin-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-all-in-one-currency-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-all-in-one-currency-public-ajax.php';

		$this->loader = new Woo_All_In_One_Currency_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_All_In_One_Currency_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_All_In_One_Currency_i18n();

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

		$plugin_admin = new Woo_All_In_One_Currency_Admin( $this->get_plugin_name(), $this->get_version() );
        $plugin_admin_ajax = new Woo_All_In_One_Currency_Admin_Ajax( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu', 40 );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'wp_ajax_wooaiocurrency_add_currency_rule', $plugin_admin_ajax, 'add_currency_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiocurrency_delete_currency_rule', $plugin_admin_ajax, 'delete_currency_rule' );
        $this->loader->add_action( 'wp_ajax_wooaiocurrency_make_base', $plugin_admin_ajax, 'make_base' );
        $this->loader->add_action( 'wp_ajax_wooaiocurrency_make_main', $plugin_admin_ajax, 'make_main' );
        $this->loader->add_action( 'wp_ajax_wooaiocurrency_add_currency_rate', $plugin_admin_ajax, 'add_currency_rate' );
        $this->loader->add_action( 'wp_ajax_wooaiocurrency_create_currency_rate', $plugin_admin_ajax, 'create_currency_rate' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_All_In_One_Currency_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'init', $plugin_public, 'set_global_currency_rule', 10 );
        // $this->loader->add_action( 'wp', $plugin_public, 'set_global_currency_rule', 10 );
        // $this->loader->add_action( 'wp_loaded', $plugin_public, 'set_global_currency_rule', 10 );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $plugin_public, 'set_woocommerce_filters', 15 );

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
	 * @return    Woo_All_In_One_Currency_Loader    Orchestrates the hooks of the plugin.
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
