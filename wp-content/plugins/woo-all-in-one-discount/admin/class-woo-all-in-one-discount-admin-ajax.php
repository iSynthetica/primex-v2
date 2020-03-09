<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Discount
 * @subpackage Woo_All_In_One_Discount/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Discount
 * @subpackage Woo_All_In_One_Discount/admin
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Discount_Admin_Ajax {

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

    public function create_product_discount_rule() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        if (empty($data['discount_title'])) {
            $response = array('message' => __('Field "Title" is required', 'woo-all-in-one-service'));
            wooaio_ajax_response('error', $response);
        }

        $create = Woo_All_In_One_Discount_Rules::create_product_discount($data);

        if (!empty($delete['error'])) {
            $response = array('message' => $delete['error']);

            wooaio_ajax_response('error', $response);
        }

        $id = $create['id'];

        $response = array(
            'message' => __('New product discount rule created', 'woo-all-in-one-discount'),
            'url' => get_admin_url(null, 'admin.php?page=wooaiodiscount&tab=discounts&discount_id=' . $id),
        );

        wooaio_ajax_response('success', $response);
    }

	public function delete_product_discount_rule() {
	    $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($id)) {
            $response = array('message' => __('Select Product discount to delete', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $single = !empty($_POST['single']) ? sanitize_text_field($_POST['single']) : 'yes';

        $delete = Woo_All_In_One_Discount_Rules::delete_product_discount($id);

        if (!empty($delete['error'])) {
            $response = array('message' => $delete['error']);

            wooaio_ajax_response('error', $response);
        }

        if ('yes' === $single) {
            $response = array(
                'message' => __('Product discount rule deleted', 'woo-all-in-one-discount'),
                'url' => get_admin_url(null, 'admin.php?page=wooaiodiscount&tab=discounts'),
            );

            wooaio_ajax_response('success', $response);
        } else {
            $response = array(
                'message' => __('Product discount rule deleted', 'woo-all-in-one-discount'),
                'reload' => 1,
            );

            wooaio_ajax_response('success', $response);
        }
    }

	public function update_product_discount_rule() {
        $setting = !empty($_POST['setting']) ? sanitize_text_field($_POST['setting']) : false;

        if (empty($setting) || empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($id)) {
            $response = array('message' => __('Select Product discount to update', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, $setting, $data);

        if (!empty($update['error'])) {
            $response = array('message' => $update['error']);

            wooaio_ajax_response('error', $response);
        }

        $response = array(
            'message' => __('Product discount rule updated', 'woo-all-in-one-discount'),
            'reload' => 1,
        );

        wooaio_ajax_response('success', $response);
    }

    public function create_discount_amount_item() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        parse_str($_POST['formData'], $form_data);
        $data = array();

        foreach ($form_data as $form_data_field => $form_data_values) {
            foreach ($form_data_values as $index => $value) {
                $data[$index][$form_data_field] = $value;
            }
        }

        $products = array();
        $categories = array();

        foreach ($data as $data_i => $data_values) {
            if (!empty($data_values['products']) && is_array($data_values['products'])) {
                if ($data_values['apply'] === 'separate_products') {
                    foreach ($data_values['products'] as $product_id) {
                        $products[$product_id] = $data_values["amount"];
                    }
                } else {
                    unset($data[$data_i]['products']);
                }
            }

            if (!empty($data_values['categories']) && is_array($data_values['categories'])) {
                if ($data_values['apply'] === 'by_categories') {
                    foreach ($data_values['categories'] as $category_id) {
                        $categories[$category_id] = $data_values["amount"];
                    }
                } else {
                    unset($data[$data_i]['categories']);
                }
            }
        }

        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'discounts', $data);
        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'products', $products);
        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'categories', $categories);

        $response = array(
            'message' => __('Product discount updated', 'woo-all-in-one-discount') . ' ' . $id,
            'reload' => 1,
        );

        wooaio_ajax_response('success', $response);
    }

    public function delete_discount_amount_item() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($_POST['formData'])) {
            $update = Woo_All_In_One_Discount_Rules::delete_product_discount_amount($id);

            $response = array(
                'message' => __('Discount amount deleted', 'woo-all-in-one-discount') . ' ' . $id,
                'reload' => 1,
            );

            wooaio_ajax_response('success', $response);
        } else {
            parse_str($_POST['formData'], $form_data);
            $data = array();

            foreach ($form_data as $form_data_field => $form_data_values) {
                foreach ($form_data_values as $index => $value) {
                    $data[$index][$form_data_field] = $value;
                }
            }

            $products = array();
            $categories = array();

            foreach ($data as $data_i => $data_values) {
                if (!empty($data_values['products']) && is_array($data_values['products'])) {
                    if ($data_values['apply'] === 'separate_products') {
                        foreach ($data_values['products'] as $product_id) {
                            $products[$product_id] = $data_values["amount"];
                        }
                    } else {
                        unset($data[$data_i]['products']);
                    }
                }

                if (!empty($data_values['categories']) && is_array($data_values['categories'])) {
                    if ($data_values['apply'] === 'by_categories') {
                        foreach ($data_values['categories'] as $category_id) {
                            $categories[$category_id] = $data_values["amount"];
                        }
                    } else {
                        unset($data[$data_i]['categories']);
                    }
                }
            }

            $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'discounts', $data);
            $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'products', $products);
            $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'categories', $categories);

            $response = array(
                'message' => __('Discount amount deleted', 'woo-all-in-one-discount') . ' ' . $id,
                'reload' => 1,
            );

            wooaio_ajax_response('success', $response);
        }
    }

    public function add_discount_amount_item() {
        $categories = Woo_All_In_One_Discount_Helpers::get_product_categories_tree();
        $products = Woo_All_In_One_Discount_Helpers::get_products_tree();
        $index = !empty($_POST['index']) ? sanitize_text_field($_POST['index']) : false;
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
	    ob_start();
        wooaiodiscount_discount_setting_item( $id, $index, $categories, $products );
	    $template = ob_get_clean();

        $response = array(
            'template' => $template,
        );

        wooaio_ajax_response('success', $response);
    }

    /**
     * Copy and save discount currency settings from currency settings
     */
    public function add_discount_currency_rate() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
        $currencyCode = !empty($_POST['currencyCode']) ? sanitize_text_field($_POST['currencyCode']) : false;

        if (empty($id) || empty($currencyCode)) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];
        $discount_currency_rule = !empty($product_discount_rule['currency']) ? $product_discount_rule['currency'] : array();

        if (empty($discount_currency_rule) || empty($discount_currency_rule[$currencyCode])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $current_currency_rule = $discount_currency_rule[$currencyCode];
        $count_rates = count($current_currency_rule['rates']);
        $categories = Woo_All_In_One_Currency_Helpers::get_product_categories_tree();
        $products = Woo_All_In_One_Currency_Helpers::get_products_tree();

        ob_start();
        wooaiodiscount_currency_rate_item( $currencyCode, $count_rates, $categories, $products, array(), $discount_currency_rule );
        $template = ob_get_clean();

        $response = array('template' => $template);

        wooaio_ajax_response('success', $response);
    }

    public function create_discount_currency_rate() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
        $currencyCode = !empty($_POST['currencyCode']) ? sanitize_text_field($_POST['currencyCode']) : false;

        if (empty($id) || empty($currencyCode)) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        if (empty($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-currency'));

            wooaio_ajax_response('error', $response);
        }

        parse_str($_POST['formData'], $form_data);

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

        $discount_rule = array(
            'rates' => $data,
            'categories' => array_unique($categories),
            'products' => array_unique($products),
        );

        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];
        $discount_currency_rule = !empty($product_discount_rule['currency']) ? $product_discount_rule['currency'] : array();
        $discount_currency_rule[$currencyCode] = $discount_rule;

        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'currency', $discount_currency_rule);

        $response = array('message' => __('User discount rule updated', 'woo-all-in-one-discount'), 'reload' => 1);

        wooaio_ajax_response('success', $response);
    }

    public function copy_discount_currency_rate() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
        $currencyCode = !empty($_POST['currencyCode']) ? sanitize_text_field($_POST['currencyCode']) : false;

        if (empty($id) || empty($currencyCode)) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $currency_rules = Woo_All_In_One_Currency_Rules::get_all();

        if (empty($currency_rules[$currencyCode])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $currency_rule = $currency_rules[$currencyCode];

        $discount_rule = array(
            'rates' => array(),
            'categories' => array(),
            'products' => array(),
        );

        if (empty($currency_rule['rates'])) {
            $response = array('message' => __('No rates set up for this currency', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $discount_rule['rates'] = $currency_rule['rates'];

        if (!empty($currency_rule['categories'])) {
            $discount_rule['categories'] = $currency_rule['categories'];
        }

        if (!empty($currency_rule['products'])) {
            $discount_rule['products'] = $currency_rule['products'];
        }

        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];
        $discount_currency_rule = !empty($product_discount_rule['currency']) ? $product_discount_rule['currency'] : array();
        $discount_currency_rule[$currencyCode] = $discount_rule;

        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'currency', $discount_currency_rule);

        $response = array('message' => __('User discount rule updated', 'woo-all-in-one-discount'), 'reload' => 1);

        wooaio_ajax_response('success', $response);
    }

    public function delete_discount_currency_rate() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
        $currencyCode = !empty($_POST['currencyCode']) ? sanitize_text_field($_POST['currencyCode']) : false;

        if (empty($id) || empty($currencyCode)) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];
        $discount_currency_rule = !empty($product_discount_rule['currency']) ? $product_discount_rule['currency'] : array();

        if (empty($discount_currency_rule) || empty($discount_currency_rule[$currencyCode])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        unset($discount_currency_rule[$currencyCode]);

        $update = Woo_All_In_One_Discount_Rules::update_product_discount($id, 'currency', $discount_currency_rule);

        $response = array('message' => __('User discount rule updated', 'woo-all-in-one-discount'), 'reload' => 1);

        wooaio_ajax_response('error', $response);
    }

    public function create_user_discount_rule() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        if (empty($data['discount_title'])) {
            $response = array('message' => __('Field "Title" is required', 'woo-all-in-one-service'));
            wooaio_ajax_response('error', $response);
        }

        $create = Woo_All_In_One_Discount_Rules::create_user_discount($data);

        if (!empty($delete['error'])) {
            $response = array('message' => $delete['error']);

            wooaio_ajax_response('error', $response);
        }

        $id = $create['id'];

        $response = array(
            'message' => __('New product discount rule created', 'woo-all-in-one-discount'),
            'url' => get_admin_url(null, 'admin.php?page=wooaiodiscount&tab=users&discount_id=' . $id),
        );

        wooaio_ajax_response('success', $response);
    }

    public function update_user_discount_rule() {
        $setting = !empty($_POST['setting']) ? sanitize_text_field($_POST['setting']) : false;

        if (empty($setting) || empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($id)) {
            $response = array('message' => __('Select User discount to update', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        $update = Woo_All_In_One_Discount_Rules::update_user_discount($id, $setting, $data);

        if (!empty($update['error'])) {
            $response = array('message' => $update['error']);

            wooaio_ajax_response('error', $response);
        }

        $response = array(
            'message' => __('User discount rule updated', 'woo-all-in-one-discount'),
            'reload' => 1,
        );

        wooaio_ajax_response('success', $response);
    }

    public function delete_user_discount_rule() {
        $id = !empty($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

        if (empty($id)) {
            $response = array('message' => __('Select User discount to delete', 'woo-all-in-one-discount'));

            wooaio_ajax_response('error', $response);
        }

        $single = !empty($_POST['single']) ? sanitize_text_field($_POST['single']) : 'yes';

        $delete = Woo_All_In_One_Discount_Rules::delete_user_discount($id);

        if (!empty($delete['error'])) {
            $response = array('message' => $delete['error']);

            wooaio_ajax_response('error', $response);
        }

        if ('yes' === $single) {
            $response = array(
                'message' => __('User discount rule deleted', 'woo-all-in-one-discount'),
                'url' => get_admin_url(null, 'admin.php?page=wooaiodiscount&tab=users'),
            );

            wooaio_ajax_response('success', $response);
        } else {
            $response = array(
                'message' => __('User discount rule deleted', 'woo-all-in-one-discount'),
                'reload' => 1,
            );

            wooaio_ajax_response('success', $response);
        }
    }
}
