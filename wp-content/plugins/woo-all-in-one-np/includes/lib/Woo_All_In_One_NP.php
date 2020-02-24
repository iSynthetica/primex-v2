<?php

function woionp_sm_init() {
    class Woo_All_In_One_NP_SM extends WC_Shipping_Method {
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'novaposhta_warehouse_warehouse';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __('NovaPoshta Warehouse - Warehouse', 'woo-all-in-one-np');
            $this->method_description = __('NovaPoshta Warehouse - Warehouse shipping method', 'woo-all-in-one-np'); // Description shown in admin
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();

            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->title      = $this->get_option( 'title' );
        }

        public function init_form_fields() {
            include_once("NovaPoshtaApi2.php");

            $this->instance_form_fields = array(
                'title'      => array(
                    'title'       => __( 'Title', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                    'default'     => $this->method_title,
                    'desc_tip'    => true,
                ),
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable NovaPostha', 'woo-all-in-one-np'),
                    'default' => 'yes'
                ),
                'description' => array(
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'desc_tip' => true,
                    'description' => __('This controls the description which the user sees during checkout.', 'woo-all-in-one-np'),
                    'default' => __("NovaPoshta Description", 'woo-all-in-one-np')
                ),
            );
        }

        public function calculate_shipping( $package = array() ) {
            $rate = array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => '',
                'calc_tax' => 'per_item'
            );

            // Register the rate
            $this->add_rate( $rate );
        }
    }
}

function woionp_sm_add_nova_poshta($methods)
{

    $methods['novaposhta_warehouse_warehouse'] = 'Woo_All_In_One_NP_SM';

    return $methods;

}

add_filter('woocommerce_shipping_methods', 'woionp_sm_add_nova_poshta');
