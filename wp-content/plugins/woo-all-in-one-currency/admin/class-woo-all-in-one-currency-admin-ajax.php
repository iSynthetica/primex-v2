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

    public function make_main() {
        $currency_code = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($currency_code)) {
            $response = array('message' => __('Select Currency to make base', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        $currency_rules = get_option('wooaio_currency_rules', array());

        if (!isset($currency_rules[$currency_code])) {
            $response = array('message' => __('Currency not exists', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        foreach ($currency_rules as $curr_code => $rules) {
            if (!empty($rules['main'])) {
                unset ($currency_rules[$curr_code]['main']);
            }
        }

        $currency_rules[$currency_code]['main'] = 1;

        update_option('wooaio_currency_rules', $currency_rules);

        $response = array(
            'message' => __('Main site currency changed', 'woo-all-in-one-currency'),
            'reload' => 1,
        );

        wooaio_ajax_response('success', $response);
    }

    /**
     * Add new selected currency rate rule item
     */
    public function add_currency_rate() {
        $currency_code = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
        $index = !empty($_POST['index']) ? sanitize_text_field($_POST['index']) : 0;

        if (empty($currency_code)) {
            $response = array('message' => __('Select Currency to add currency rate', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        $categories = Woo_All_In_One_Currency_Helpers::get_product_categories_tree();
        $products = Woo_All_In_One_Currency_Helpers::get_products_tree();

        ob_start();
        wooaiocurrency_currency_rate_item( $currency_code, $index, $categories, $products );
        $template = ob_get_clean();

        $response = array(
            'template' => $template,
        );

        wooaio_ajax_response('success', $response);
    }

    /**
     * Ajax create, update, delete selected currency rates rules
     */
    public function create_currency_rate() {
        if (empty($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        parse_str($_POST['formData'], $form_data);

        if (empty($form_data['currency_code'])) {
            $response = array('message' => __('Select currency, please!', 'woo-all-in-one-currency'));
            wooaio_ajax_response('error', $response);
        }

        $currency_code = $form_data['currency_code'];
        unset($form_data['currency_code']);

        $data = array();
        $products = array();
        $categories = array();

        foreach ($form_data as $form_data_field => $form_data_values) {
            foreach ($form_data_values as $index => $value) {
                $data[$index][$form_data_field] = $value;
            }
        }

        foreach ($data as $data_settings) {
            if (empty($data_settings['rate'])) {
                $response = array('message' => __('Set currency rate amount!', 'woo-all-in-one-currency'));
                wooaio_ajax_response('error', $response);
            }

            if ('specified_categories' === $data_settings["apply"]) {
                if (empty($data_settings["categories"])) {
                    $response = array('message' => __('Select at least one category!', 'woo-all-in-one-currency'));
                    wooaio_ajax_response('error', $response);
                }

                $categories = array_merge($categories, $data_settings["categories"]);
            }

            if ('specified_products' === $data_settings["apply"]) {
                if (empty($data_settings["products"])) {
                    $response = array('message' => __('Select at least one product!', 'woo-all-in-one-currency'));
                    wooaio_ajax_response('error', $response);
                }

                $products = array_merge($products, $data_settings["products"]);
            }
        }

        $update_data = array(
            'rates' => $data,
            'categories' => array_unique($categories),
            'products' => array_unique($products),
        );

        $update = Woo_All_In_One_Currency_Rules::update($currency_code, $update_data);

        if (!empty($update['error'])) {
            $response = array('message' => $update['error']);
            wooaio_ajax_response('error', $response);
        }

        $response = array(
            'message' => __('Rate rules for current currency updated!', 'woo-all-in-one-currency'),
            'reload' => 1,
        );
        wooaio_ajax_response('success', $response);
    }
}
