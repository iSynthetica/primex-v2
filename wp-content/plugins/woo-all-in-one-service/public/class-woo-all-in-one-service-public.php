<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Service_Public {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-service-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-service-public.js', array( 'jquery' ), $this->version, true );

        wp_localize_script( $this->plugin_name, 'wooaioserviceJsObj', array(
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'snth_nonce' )
        ) );

	}

	public function template_hooks() {
	    add_action('wooaioservice_repairs_content', 'wooaioservice_repairs_content', 10);
	    add_action('wooaioservice_before_fields', 'wooaioservice_before_fields', 10);
	    add_action('wooaioservice_after_fields', 'wooaioservice_after_fields', 10);
    }

    public function send_email($id) {
        $mailer = WC()->mailer();
        $email = $mailer->emails['Woo_All_In_One_Service_Email_Customer'];
        $email->trigger( $id );
    }

    public function shortcodes() {
        add_shortcode( 'wooaioservice_customer_form', array($this, 'customer_form') );
    }

    public function customer_form() {
	    ob_start();
        include (WOO_ALL_IN_ONE_SERVICE_PATH . 'woocommerce/repairs/repairs-form.php');
	    ob_get_clean();
	    return ob_get_clean();
    }
}
