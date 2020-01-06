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
class Woo_All_In_One_Service_Admin_Ajax {

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

	public function delete_access_rule() {
        $access_settings = get_option('wooaioservice_access_settings', array());
        $role = !empty($_POST['role']) ? sanitize_text_field($_POST['role']) : false;

        if (empty($role)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select role to delete', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        if (!empty($access_settings[$role])) {
            unset($access_settings[$role]);
        }

        if (empty(count($access_settings))) {
            delete_option('wooaioservice_access_settings');
        } else {
            update_option('wooaioservice_access_settings', $access_settings);
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Access Rule deleted', 'woo-all-in-one-service'));

        echo json_encode($response);
        wp_die();
    }

	public function edit_access_rule() {
        $access_settings = get_option('wooaioservice_access_settings', array());
        $role = !empty($_POST['role']) ? sanitize_text_field($_POST['role']) : false;

        if (empty($role)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select role to delete', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        if (empty($data["wooaioservice_access_level"])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select access level', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        if (!empty($access_settings[$role])) {
            $access_settings[$role] = array(
                'rule' => $data["wooaioservice_access_level"],
            );

            update_option('wooaioservice_access_settings', $access_settings);

            $response = array('success' => 1, 'error' => 0, 'message' => __('Access Rule updated', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        } else {
            $access_settings[$role] = array(
                'rule' => $data["wooaioservice_access_level"],
            );

            update_option('wooaioservice_access_settings', $access_settings);

            $response = array('success' => 1, 'error' => 0, 'message' => __('Access Rule created', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }
    }

	public function create_access_rule() {
        $access_settings = get_option('wooaioservice_access_settings', array());

        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Cheating, huh!!!', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        if (empty($data["wooaioservice-access-role"])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select user role', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        if (empty($data["wooaioservice_access_level"])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select access level', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        $access_settings[$data["wooaioservice-access-role"]] = array(
            'rule' => $data["wooaioservice_access_level"],
        );

        update_option('wooaioservice_access_settings', $access_settings);

        $response = array('success' => 1, 'error' => 0, 'message' => __('Access Rule created', 'woo-all-in-one-service'));

        echo json_encode($response);
        wp_die();
    }

	public function service_delete() {
        $id = !empty($_POST['repairId']) ? sanitize_text_field($_POST['repairId']) : false;
        $single = !empty($_POST['single']) ? sanitize_text_field($_POST['single']) : false;

        if (empty($id)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Select repair to delete', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        Woo_All_In_One_Service_Model::delete($id);

        if (!empty($single)) {
            $response = array('success' => 1, 'error' => 0, 'message' => __('Repair deleted', 'woo-all-in-one-service'), 'url' => get_admin_url(null, 'admin.php?page=wooaioservice&tab=repairs'));

            echo json_encode($response);
            wp_die();
        } else {
            $response = array('success' => 1, 'error' => 0, 'message' => __('Repair deleted', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }
    }

	public function service_edit() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Cheating, huh!!!', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        if (empty($data['repair_id'])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Cheating, huh!!!', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        $repair_id = $data['repair_id'];
        unset($data['repair_id']);

        $validation = Woo_All_In_One_Service_Form::validate_form_fields($data);

        $where = array('ID' => $repair_id);
        $repairs = Woo_All_In_One_Service_Model::get($where);
        $old_repair = $repairs[0];
        $new_status = $validation['data']["repair_status"];
        $old_status = $old_repair["status"];

        $id = Woo_All_In_One_Service_Model::update($repair_id, $validation['data']);

        if (!$id) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Cheating, huh!!!', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        if ($new_status !== $old_status) {
            do_action( 'wooaioservice_status_changed', $id, $new_status, $old_status );
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Repair updated', 'woo-all-in-one-service'));

        echo json_encode($response);
        wp_die();
    }
}
