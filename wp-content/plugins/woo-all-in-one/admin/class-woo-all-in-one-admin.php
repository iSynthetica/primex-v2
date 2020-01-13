<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One
 * @subpackage Woo_All_In_One/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One
 * @subpackage Woo_All_In_One/admin
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Admin {

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
		wp_enqueue_style( $this->plugin_name . '-grid', plugin_dir_url( __FILE__ ) . 'css/wooaio-flexboxgrid.css', array(), '0.6.3', 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function admin_menu() {
        add_menu_page(
            __('Woo All In One Main Page', 'woo-all-in-one'),
            __('Woo All In One', 'woo-all-in-one'),
            'manage_options',
            'wooaio',
            null,
            'dashicons-vault',
            15
        );

        add_submenu_page(
            'wooaio',
            __('Woo All In One Main Page', 'woo-all-in-one'),
            __('Woo All In One', 'woo-all-in-one'),
            'manage_options',
            'wooaio',
            array($this, 'render_settings_page'),
            5
        );
    }

    public function render_settings_page() {
        include(WOO_ALL_IN_ONE_ADMIN. '/partials/woo-all-in-one-admin-display.php');
    }
}
