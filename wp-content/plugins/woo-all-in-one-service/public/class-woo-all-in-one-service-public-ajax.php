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
class Woo_All_In_One_Service_Public_Ajax {
    public function wooaioservice_submit() {

        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Cheating, huh!!!', 'woo-all-in-one-service'));

            echo json_encode($response);
            wp_die();
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = sanitize_text_field($form_data['value']);
        }

        $id = Woo_All_In_One_Service_Model::create($data);

        $response = array('success' => 1, 'error' => 0, 'message' => __('Repair request created with ID ' . $id, 'woo-all-in-one-service'));

        echo json_encode($response);
        wp_die();
    }
}
