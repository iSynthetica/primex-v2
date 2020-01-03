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
                'label'             => __( 'Дата приема заявки', 'woo-all-in-one-service' ),
                'custom_attributes' => array('readonly' => 'true'),
                'class'             => array('form-row-wide'),
                'validate'          => array('date'),
                'priority'     => 40,
            ),
            'repair_order_date' => array(
                'label'        => __( 'Дата покупки', 'woo-all-in-one-service' ),
                'custom_attributes' => array('readonly' => 'true', 'date' => 'date'),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'validate'     => array('date'),
                'priority'     => 50,
            ),
            'repair_np_ttn' => array(
                'label'        => __( 'Номер накладной Новой Почты', 'woo-all-in-one-service' ),
                'class'        => array('form-row-wide'),
            ),
            'repair_product' => array(
                'label'        => __( 'Модель или товар', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'priority'     => 60,
            ),
            'repair_serial' => array(
                'label'        => __( 'Серийный номер', 'woo-all-in-one-service' ),
                'class'        => array('form-row-wide'),
                'priority'     => 70,
            ),
            'repair_state' => array(
                'label'        => __( 'Внешнее состояние', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'priority'     => 80,
            ),
            'repair_set' => array(
                'label'        => __( 'Комплектация', 'woo-all-in-one-service' ),
                'required'     => true,
                'type'         => 'textarea',
                'class'        => array('form-row-wide'),
                'priority'     => 90,
            ),
            'repair_fault' => array(
                'label'        => __( 'Заявленная неисправность', 'woo-all-in-one-service' ),
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
            } else {
                $form_field = $form_fields[$dk];

                $type = !empty($form_field['type']) ? $form_field['type'] : 'text';

                switch ($type) {
                    case 'textarea':
                        $value = sanitize_textarea_field($dv);
                        break;
                    default:
                        $value = sanitize_textarea_field($dv);
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

    public static function get_repairs_statuses() {
        return array(
                'wait' => __('Ожидание', 'woo-all-in-one-service'),
                'get' => __('Получен из Новой Почты/Принят в офисе', 'woo-all-in-one-service'),
                'process' => __('В работе', 'woo-all-in-one-service'),
                'repaired' => __('Получен из сервиса', 'woo-all-in-one-service'),
                'sent' => __('Отправлен покупателю', 'woo-all-in-one-service'),
                'closed' => __('Закрыт', 'woo-all-in-one-service'),
        );
    }
}