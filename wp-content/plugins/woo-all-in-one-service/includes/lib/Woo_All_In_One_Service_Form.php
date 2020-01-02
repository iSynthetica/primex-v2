<?php


class Woo_All_In_One_Service_Form {
    public static function get_form_fields_values() {
        $name = '';

        $fn = self::get_form_user_data('billing_first_name');
        $ln = self::get_form_user_data('billing_last_name');
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
        return apply_filters( 'wooaioservice_fields', array (
            'repair_name'  =>
                array(
                    'label'        => __( 'First name', 'woocommerce' ) . ' ' . __( 'Last name', 'woocommerce' ),
                    'required'     => true,
                    'class'        =>
                        array(
                            0 => 'form-row-first',
                        ),
                    'autocomplete' => 'given-name',
                ),
            'repair_phone' =>
                array(
                    'label'        => __( 'Phone', 'woocommerce' ),
                    'required'     => true,
                    'type'         => 'tel',
                    'class'        =>
                        array(
                            0 => 'form-row-wide',
                        ),
                    'validate'     =>
                        array(
                            0 => 'phone',
                        ),
                    'autocomplete' => 'tel',
                ),
            'repair_email' => array(
                'label'        => __( 'Email address', 'woocommerce' ),
                'required'     => true,
                'type'         => 'email',
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_created_date' => array(
                'label'        => __( 'Дата приема заявки', 'woo-all-in-one-service' ),
                'custom_attributes' => array('readonly' => 'true'),
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_order_date' => array(
                'label'        => __( 'Дата покупки', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_np_ttn' => array(
                'label'        => __( 'Номер накладной Новой Почты', 'woo-all-in-one-service' ),
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_product' => array(
                'label'        => __( 'Модель или товар', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_serial' => array(
                'label'        => __( 'Серийный номер', 'woo-all-in-one-service' ),
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_state' => array(
                'label'        => __( 'Внешнее состояние', 'woo-all-in-one-service' ),
                'required'     => true,
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_set' => array(
                'label'        => __( 'Комплектация', 'woo-all-in-one-service' ),
                'required'     => true,
                'type'         => 'textarea',
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            ),
            'repair_fault' => array(
                'label'        => __( 'Заявленная неисправность', 'woo-all-in-one-service' ),
                'required'     => true,
                'type'         => 'textarea',
                'class'        =>
                    array(
                        0 => 'form-row-wide',
                    ),
                'validate'     =>
                    array(
                        0 => 'email',
                    ),
                'autocomplete' => 'email',
            )
        ));
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
}