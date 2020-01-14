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

        $currency_code = Woo_All_In_One_Currency_Rules::create($data);

        if (empty($currency_code['error'])) {
            $response = array('message' => $currency_code['error']);
            wooaio_ajax_response('error', $response);
        }

        $response = array('message' => __('Success', 'woo-all-in-one-currency'));

        wooaio_ajax_response('success', $response);
	}
}
