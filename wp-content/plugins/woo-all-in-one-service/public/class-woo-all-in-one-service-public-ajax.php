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
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        $validation = Woo_All_In_One_Service_Form::validate_form_fields($data);

        if (!empty($validation['error'])) {
            $response = array(
                'success' => 0,
                'error' => 1,
                'messages' => __('Validation error', 'woo-all-in-one-service'),
                'fragments' => array(
                    '#wooaioservice_messages_container' => Woo_All_In_One_Service_Form::get_validation_errors($validation['error'])
                ),
                'scrollToFragment' => '.woocommerce-MyAccount-content',
            );

            echo json_encode($response);
            wp_die();
        }

        $id = Woo_All_In_One_Service_Model::create($validation['data']);

        do_action( 'wooaioservice_created', $id );
        $fields = Woo_All_In_One_Service_Form::get_form_fields();
        $fields_values = Woo_All_In_One_Service_Form::get_form_fields_values();
        $where = array('author' => get_current_user_id());
        $repairs = Woo_All_In_One_Service_Model::get($where);

        ob_start();
        include (WOO_ALL_IN_ONE_SERVICE_PATH . 'woocommerce/repairs/repairs-form.php');
        $repair_form = ob_get_clean();

        ob_start();
        include (WOO_ALL_IN_ONE_SERVICE_PATH . 'woocommerce/repairs/repairs-table.php');
        $repair_table = ob_get_clean();

        $where = array('ID' => $id);
        $repairs = Woo_All_In_One_Service_Model::get($where);

        $fragments = array(
            '#wooaioservice_container' => $repair_form,
            '#wooaioservice_list_container' => $repair_table,
            '#wooaioservice_messages_container' => Woo_All_In_One_Service_Form::get_success_message($repairs[0]['title']),
        );

        $response = array(
            'success' => 1,
            'error' => 0,
            'message' => __('Repair request created with ID ' . $id, 'woo-all-in-one-service'),
            'fragments' => $fragments,
            'scrollToFragment' => '.woocommerce-MyAccount-content',
        );

        echo json_encode($response);
        wp_die();
    }
}
