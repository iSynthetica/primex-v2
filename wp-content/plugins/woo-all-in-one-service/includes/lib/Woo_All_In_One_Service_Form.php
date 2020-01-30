<?php


class Woo_All_In_One_Service_Form {
    public static function get_form_fields_values() {
        $current_user = wp_get_current_user();

        $name = '';

        $fn = $current_user->user_firstname;
        $ln = $current_user->user_lastname;
        $phone = self::get_form_user_data('billing_phone');
        $email = self::get_form_user_data('billing_email');

        return array (
            'repair_name'  => array(
                'value'        => $fn . ' ' . $ln,
            ),
            'repair_phone' => array(
                'value'        => $phone
            ),
            'repair_email' => array(
                'value'        => $email,
            ),
            'repair_created_date' => array(
                'value'        => date('d-m-Y', time()),
            )
        );
    }

    public static function get_form_fields() {
        $fields = apply_filters( 'wooaioservice_fields', array (
            'repair_name'  => array(
                'label'        => __( 'First name', 'woocommerce' ) . ' ' . __( 'Last name', 'woocommerce' ),
                'required'     => true,
                'class'        => array('form-row-first'),
                'priority'     => 10,
            ),
            'repair_phone' => array(
                'label'        => __( 'Phone', 'woocommerce' ),
                'required'     => true,
                'type'         => 'tel',
                'class'        => array('form-row-wide'),
                'validate'     => array('phone'),
                'priority'     => 20,
            ),
            'repair_email' => array(
                'label'        => __( 'Email address', 'woocommerce' ),
                'required'     => true,
                'type'         => 'email',
                'class'        => array('form-row-wide'),
                'validate'     => array('email'),
                'priority'     => 30,
            ),
            'repair_created_date' => array(
                'label'             => __( 'Date of request', 'woo-all-in-one-service' ),
                'custom_attributes' => array('readonly' => 'true'),
                'class'             => array('form-row-wide'),
                'validate'          => array('date'),
                'priority'     => 40,
            ),
            'repair_order_date' => array(
                'label'        => __( 'Order date', 'woo-all-in-one-service' ),
                'custom_attributes' => array('readonly' => 'true', 'date' => 'date'),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'validate'     => array('date'),
                'priority'     => 50,
            ),
            'repair_np_ttn' => array(
                'label'        => __( 'NP waybill number', 'woo-all-in-one-service' ),
                'class'        => array('form-row-wide'),
            ),
            'repair_product' => array(
                'label'        => __( 'Product or model', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'priority'     => 60,
            ),
            'repair_serial' => array(
                'label'        => __( 'Serial number', 'woo-all-in-one-service' ),
                'class'        => array('form-row-wide'),
                'priority'     => 70,
            ),
            'repair_state' => array(
                'label'        => __( 'Condition', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'priority'     => 80,
            ),
            'repair_set' => array(
                'label'        => __( 'Set included', 'woo-all-in-one-service' ),
                'required'     => true,
                'type'         => 'textarea',
                'class'        => array('form-row-wide'),
                'priority'     => 90,
            ),
            'repair_fault' => array(
                'label'        => __( 'Declared malfunction', 'woo-all-in-one-service' ),
                'required'     => true,
                'type'         => 'textarea',
                'class'        => array('form-row-wide'),
                'priority'     => 100,
            )
        ));

        uasort( $fields, 'wc_checkout_fields_uasort_comparison' );

        return $fields;
    }

    public static function get_form_user_data($input) {
        if ( !is_user_logged_in() ) {
            return false;
        }

        $customer_object = false;

        if ( is_user_logged_in() ) {
            $customer_object = new WC_Customer( get_current_user_id(), true );
        }

        if ( ! $customer_object ) {
            $customer_object = WC()->customer;
        }

        if ( is_callable( array( $customer_object, "get_$input" ) ) ) {
            $value = $customer_object->{"get_$input"}();
        } elseif ( $customer_object->meta_exists( $input ) ) {
            $value = $customer_object->get_meta( $input, true );
        }

        if ( '' === $value ) {
            $value = null;
        }

        return apply_filters( 'default_checkout_' . $input, $value, $input );
    }

    public static function validate_form_fields($data) {
        $form_fields = self::get_form_fields();
        $error_array = array();

        foreach ($data as $dk => $dv) {
            if ('repair_author' === $dk) {
                if (empty($dv)) {
                    $error_array[$dk] = 'Cheating, hah!!!';
                } elseif ((int) $dv !== (int) get_current_user_id()) {
                    $error_array[$dk] = 'Cheating, hah!!!';
                }

                if (!empty($error_array)) {
                    return array(
                        'error' => $error_array,
                        'data' => $data,
                    );
                }
                $value = sanitize_text_field($dv);
                $data[$dk] = $value;
            } elseif ('repair_result' === $dk) {
                $value = sanitize_textarea_field($dv);
                $data[$dk] = $value;
            } else {
                $form_field = $form_fields[$dk];

                $type = !empty($form_field['type']) ? $form_field['type'] : 'text';

                switch ($type) {
                    case 'textarea':
                        $value = sanitize_textarea_field($dv);
                        break;
                    default:
                        $value = sanitize_text_field($dv);
                }

                if (!empty($form_field['required']) && empty($value)) {
                    $error_array[$dk] = sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $form_field['label'] ) . '</strong>' );
                } else {
                    if (!empty($form_field['validate'])) {
                        foreach ($form_field['validate'] as $validate) {
                            if ('email' === $validate) {
                                $email_is_valid = is_email( $value );

                                if (!$email_is_valid) {
                                    $error_array[$dk] = sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . esc_html( $form_field['label'] ) . '</strong>' );
                                } else {
                                    $data[$dk] = $value;
                                }
                            } elseif ('phone' === $validate) {
                                $is_phone = WC_Validation::is_phone( $value );

                                if (!$is_phone) {
                                    $error_array[$dk] = sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . esc_html( $form_field['label'] ) . '</strong>' );
                                } else {
                                    $data[$dk] = $value;
                                }
                            } elseif ('date' === $validate) {
                                $timestamp = strtotime($value);

                                if (!$timestamp) {
                                    $error_array[$dk] = sprintf( __( '%s is not a valid date format.', 'woo-all-in-one-service' ), '<strong>' . esc_html( $form_field['label'] ) . '</strong>' );
                                } else {
                                    $data[$dk] = gmdate( 'Y-m-d H:i:s', $timestamp);
                                }
                            } else {
                                $data[$dk] = $value;
                            }
                        }
                    }
                }
            }
        }

        return array(
            'error' => $error_array,
            'data' => $data,
        );
    }

    public static function get_validation_errors($errors) {
        ob_start();
        ?>
        <div id="wooaioservice_messages_container">
            <ul class="woocommerce-error" role="alert">
                <?php
                foreach ($errors as $error) {
                    ?>
                    <li><?php echo $error; ?></li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function get_success_message($title) {
        ob_start();
        ?>
        <div id="wooaioservice_messages_container">
            <ul class="woocommerce-success" role="alert">
                <li><?php echo __('Repaire request created successfully with number', 'woo-all-in-one-service'); ?> <strong><?php echo $title; ?></strong></li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function get_repairs_statuses() {
        return array(
                'wait' => __('Wait', 'woo-all-in-one-service'),
                'get' => __('Got on NP/Accepted in the office', 'woo-all-in-one-service'),
                'process' => __('In Progress', 'woo-all-in-one-service'),
                'repaired' => __('Got from Service', 'woo-all-in-one-service'),
                'sent' => __('Sent to customer', 'woo-all-in-one-service'),
                'closed' => __('Closed', 'woo-all-in-one-service'),
        );
    }

    public static function get_email_repair_data($repair) {
        $data_array = array();
        $title = !empty($repair['title']) ? $repair['title'] : '';

        $data_array['title'] = array(
            'label' => __( 'Repair request #', 'woo-all-in-one-service' ),
            'value' => $title,
            'type' => 'text',
        );

        $name = !empty($repair['name']) ? $repair['name'] : '';

        $data_array['name'] = array(
            'label' => __( 'Name', 'woocommerce' ),
            'value' => $name,
            'type' => 'text',
        );

        $phone = !empty($repair['phone']) ? $repair['phone'] : '';

        $data_array['phone'] = array(
            'label' => __( 'Phone', 'woocommerce' ),
            'value' => $phone,
            'type' => 'text',
        );

        $email = !empty($repair['email']) ? $repair['email'] : '';

        $data_array['email'] = array(
            'label' => __( 'Email address', 'woocommerce' ),
            'value' => $email,
            'type' => 'text',
        );

        $created = !empty($repair['created']) ? $repair['created'] : '';
        $timestamp = strtotime($created);
        if (!$timestamp) {
            $created = '';
        } else {
            $created = gmdate( 'd-m-Y', $timestamp);
        }

        $data_array['created'] = array(
            'label' => __( 'Date of request', 'woo-all-in-one-service' ),
            'value' => $created,
            'type' => 'text',
        );

        $modified = !empty($repair['modified']) ? $repair['modified'] : '';
        $timestamp = strtotime($modified);
        if (!$timestamp) {
            $modified = '';
        } else {
            $modified = gmdate( 'd-m-Y', $timestamp);
        }

        $data_array['modified'] = array(
            'label' => __( 'Date of status changed', 'woo-all-in-one-service' ),
            'value' => $modified,
            'type' => 'text',
        );

        $order_date = !empty($repair['order_date']) ? $repair['order_date'] : '';
        $timestamp = strtotime($order_date);

        if (!$timestamp) {
            $order_date = '';
        } else {
            $order_date = gmdate( 'd-m-Y', $timestamp);
        }

        $data_array['order_date'] = array(
            'label' => __( 'Order date', 'woo-all-in-one-service' ),
            'value' => $order_date,
            'type' => 'text',
        );

        $np_ttn = !empty($repair['np_ttn']) ? $repair['np_ttn'] : '';

        $data_array['np_ttn'] = array(
            'label' => __( 'NP waybill number', 'woo-all-in-one-service' ),
            'value' => $np_ttn,
            'type' => 'text',
        );

        $product_title = !empty($repair['product']) ? $repair['product'] : '';

        $data_array['product_title'] = array(
            'label' => __( 'Product or model', 'woo-all-in-one-service' ),
            'value' => $product_title,
            'type' => 'text',
        );

        $serial = !empty($repair['serial']) ? $repair['serial'] : '';

        $data_array['serial'] = array(
            'label' => __( 'Serial number', 'woo-all-in-one-service' ),
            'value' => $serial,
            'type' => 'text',
        );

        $state = !empty($repair['state']) ? $repair['state'] : '';

        $data_array['state'] = array(
            'label' => __( 'Condition', 'woo-all-in-one-service' ),
            'value' => $state,
            'type' => 'text',
        );

        $set = !empty($repair['set']) ? $repair['set'] : '';

        $data_array['set'] = array(
            'label' => __( 'Set included', 'woo-all-in-one-service' ),
            'value' => $set,
            'type' => 'textarea',
        );

        $fault = !empty($repair['fault']) ? $repair['fault'] : '';

        $data_array['fault'] = array(
            'label' => __( 'Declared malfunction', 'woo-all-in-one-service' ),
            'value' => $fault,
            'type' => 'textarea',
        );

        $status = ! empty( $repair['status'] ) ? $repair['status'] : '';

        $data_array['status'] = array(
            'label' => __( 'Repair status', 'woo-all-in-one-service' ),
            'value' => $status,
            'type' => 'text',
        );

        $result = !empty($repair['result']) ? $repair['result'] : '';

        $data_array['result'] = array(
            'label' => __( 'Repair result', 'woo-all-in-one-service' ),
            'value' => $result,
            'type' => 'textarea',
        );

        return $data_array;
    }

    public static function get_email_repair_data_by_sections($repair) {
        $email_array = Woo_All_In_One_Service_Form::get_email_repair_data($repair);

        $service_info = array();
        $service_info['title'] = $email_array['title'];
        $service_info['modified'] = $email_array['modified'];

        $customer_info = array();
        $customer_info['name'] = $email_array['name'];
        $customer_info['phone'] = $email_array['phone'];
        $customer_info['email'] = $email_array['email'];
        $customer_info['created'] = $email_array['created'];
        $customer_info['order_date'] = $email_array['order_date'];
        $customer_info['np_ttn'] = $email_array['np_ttn'];

        $product_info = array();
        $product_info['product_title'] = $email_array['product_title'];
        $product_info['serial'] = $email_array['serial'];
        $product_info['state'] = $email_array['state'];
        $product_info['set'] = $email_array['set'];
        $product_info['fault'] = $email_array['fault'];

        $repair_info = array();
        $repair_info['status'] = $email_array['status'];
        $repair_info['result'] = $email_array['result'];

        return array(
            'service-info' => array(
                'label' => __('Service Info', 'woo-all-in-one-service'),
                'data' => $service_info,
            ),
            'customer-info' => array(
                'label' => __('Customer Info', 'woo-all-in-one-service'),
                'data' => $customer_info,
            ),
            'product-info' => array(
                'label' => __('Product Info', 'woo-all-in-one-service'),
                'data' => $product_info,
            ),
            'repair-info' => array(
                'label' => __('Repair Info', 'woo-all-in-one-service'),
                'data' => $repair_info,
            ),
        );
    }
}