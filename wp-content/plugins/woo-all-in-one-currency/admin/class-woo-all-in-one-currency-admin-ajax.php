<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Currency
 * @subpackage Woo_All_In_One_Currency/admin
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Currency_Admin_Ajax {

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

	public function add_currency_rule() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        if (empty($data['currency_code'])) {
            $response = array('message' => __('Select currency, please!', 'woo-all-in-one-currency'));
            wooaio_ajax_response('error', $response);
        }

        $create = Woo_All_In_One_Currency_Rules::create($data);

        if (!empty($create['error'])) {
            $response = array('message' => $create['error']);
            wooaio_ajax_response('error', $response);
        }

        $response = array(
            'message' => __('New currency rule created', 'woo-all-in-one-currency'),
            'url' => get_admin_url(null, 'admin.php?page=wooaiocurrency&tab=currency&currency_code=' . $create['currency_code']),
        );

        wooaio_ajax_response('success', $response);
	}

	public function delete_currency_rule() {
        $currency_code = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($currency_code)) {
            $response = array('message' => __('Select Currency to delete', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        $delete = Woo_All_In_One_Currency_Rules::delete($currency_code);

        if (!empty($delete['error'])) {
            $response = array('message' => $delete['error']);

            wooaio_ajax_response('error', $response);
        }

        $response = array(
            'message' => __('Currency rule deleted', 'woo-all-in-one-currency'),
            'url' => get_admin_url(null, 'admin.php?page=wooaiocurrency&tab=currency'),
        );

        wooaio_ajax_response('success', $response);
    }

	public function make_base() {
        $currency_code = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($currency_code)) {
            $response = array('message' => __('Select Currency to make base', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        update_option( 'woocommerce_currency', $currency_code );

        $response = array(
            'message' => __('Base currency changed', 'woo-all-in-one-currency'),
            'url' => get_admin_url(null, 'admin.php?page=wooaiocurrency&tab=currency'),
        );

        wooaio_ajax_response('success', $response);
    }

    public function add_currency_rate() {
        $currency_code = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($currency_code)) {
            $response = array('message' => __('Select Currency to add currency rate', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        $response = array(
            'message' => __('Currency rate set up', 'woo-all-in-one-currency'),
            // 'url' => get_admin_url(null, 'admin.php?page=wooaiocurrency&tab=currency'),
        );

        wooaio_ajax_response('success', $response);
    }
}
