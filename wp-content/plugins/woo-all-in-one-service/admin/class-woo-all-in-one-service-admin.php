<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/admin
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Service_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_All_In_One_Service_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_All_In_One_Service_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-service-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_All_In_One_Service_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_All_In_One_Service_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-service-admin.js', array( 'jquery' ), $this->version, true );

	}

	public function init() {
        $this->check_version();
    }

    public function check_version() {
        $version = get_option( 'wooaioservice_version' );

        if ( empty($version) || version_compare( $version, WOO_ALL_IN_ONE_SERVICE_VERSION, '<' ) ) {
            $this->install();
            do_action( 'wooaioservice_updated' );
        }
    }

    public function install() {
        Woo_All_In_One_Service_Model::create_repairs_table();
        Woo_All_In_One_Service_Model::create_repairsmeta_table();
        $this->update_version();
    }

    public function update_version() {
        delete_option( 'wooaioservice_version' );
        add_option( 'wooaioservice_version', WOO_ALL_IN_ONE_SERVICE_VERSION );
    }

	public function admin_menu() {
        add_menu_page(
            __('Repairs', 'woo-all-in-one-service'),
            __('Product Repairs', 'woo-all-in-one-service'),
            'manage_options',
            'wooaioservice',
            array($this, 'render_settings_page'),
            'dashicons-hammer',
            12
        );
    }

    public function render_settings_page() {
	    $allowed_tabs = Woo_All_In_One_Service_Helpers::get_allowed_tabs();
	    $allowed_tabs_keys = array_keys($allowed_tabs);
        $default_tab = 'repairs';
        $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : $default_tab;

        if (!in_array($active_tab, $allowed_tabs_keys)) {
            $active_tab = $default_tab;
        }

        include(dirname(__FILE__) . '/partials/woo-all-in-one-service-admin-display.php');
    }
}
