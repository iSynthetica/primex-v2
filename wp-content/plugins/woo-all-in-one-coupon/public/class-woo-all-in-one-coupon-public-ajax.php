<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Coupon
 * @subpackage Woo_All_In_One_Coupon/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Coupon
 * @subpackage Woo_All_In_One_Coupon/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Coupon_Public_Ajax {

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

	public function wooaiocoupon_submit() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-coupon'));

            wooaio_ajax_response('error', $response);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        $validation = Woo_All_In_One_Coupon_Form::validate_form($data);

        if (!empty($validation['error'])) {
            $response = array('messageHtml' => wooaiocoupon_get_form_messages($validation['error'], 'error'));
            wooaio_ajax_response('error', $response);
        }
        $data = $validation['data'];

        $coupon_id = Woo_All_In_One_Coupon_Model::generate_coupon($data);

        if ($coupon_id) {
            $email = $data['coupon_email'];
            $send_email = Woo_All_In_One_Coupon_Model::send_email_to_client($coupon_id, $email);

            if ($send_email) {
                $response = array('messageHtml' => wooaiocoupon_get_form_messages(array(__('Thank you, please check your email.', 'woo-all-in-one-coupon'))));
                wooaio_ajax_response('success', $response);
            }
        }

        $response = array('messageHtml' => wooaiocoupon_get_form_messages(array(__("We can't generate coupon right now, please try again later.", 'woo-all-in-one-coupon')), 'error'));
        wooaio_ajax_response('error', $response);
    }
}
