<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Turbosms
 * @subpackage Woo_All_In_One_Turbosms/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Turbosms
 * @subpackage Woo_All_In_One_Turbosms/admin
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Turbosms_Admin {

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
		if (!empty($_GET['page']) && 'wooaioturbosms' === $_GET['page']) {
            if (!function_exists('run_woo_all_in_one')) {
                wp_enqueue_style( $this->plugin_name . '-grid', plugin_dir_url( __FILE__ ) . 'css/wooaio-flexboxgrid.css', array(), '0.6.3', 'all' );
			}
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-turbosms-admin.css', array(), $this->version, 'all' );
        }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-turbosms-admin.js', array( 'jquery' ), $this->version, true );
	}

	public function admin_menu() {
        if (function_exists('run_woo_all_in_one') && current_user_can( 'manage_options' )) {
            add_submenu_page(
                'wooaio',
                __('TurboSMS', 'woo-all-in-one-turbosms'),
                __('TurboSMS  Settings', 'woo-all-in-one-turbosms'),
                'manage_options',
                'wooaioturbosms',
                array($this, 'render_settings_page'),
                10
            );
        } else {
            add_menu_page(
                __('TurboSMS', 'woo-all-in-one-turbosms'),
                __('TurboSMS Settings', 'woo-all-in-one-turbosms'),
                'manage_options',
                'wooaioturbosms',
                array($this, 'render_settings_page'),
                'dashicons-hammer',
                12
            );
        }
	}
	
	public function render_settings_page() {
        $allowed_tabs = Woo_All_In_One_Turbosms_Helpers::get_allowed_tabs();
        $allowed_tabs_keys = array_keys($allowed_tabs);
        $default_tab = 'settings';
        $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : $default_tab;

        if (!in_array($active_tab, $allowed_tabs_keys)) {
            $active_tab = $default_tab;
        }

		include( WOO_ALL_IN_ONE_TURBOSMS_ADMIN . '/partials/admin-display.php' );
	}

	public function ajax_settings_update() {
        $setting = !empty($_POST['setting']) ? sanitize_text_field($_POST['setting']) : false;

        if (empty($setting) || empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
		}

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }
		
		$update = Woo_All_In_One_Turbosms_Helpers::update_settings($data);

        $response = array(
            'message' => __('Product discount rule updated', 'woo-all-in-one-discount'),
            'reload' => 1,
        );

        wooaio_ajax_response('success', $response);
	}
}
