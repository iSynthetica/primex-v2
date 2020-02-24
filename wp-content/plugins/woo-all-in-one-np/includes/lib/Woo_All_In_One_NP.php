<?php

function woionp_sm_init() {
    class Woo_All_In_One_NP_SM extends WC_Shipping_Method {
        public function __construct() {
            $this->id = 'novaposhta'; // Id for your shipping method. Should be uunique.
            $this->method_title = __('NovaPoshta', 'woo-all-in-one-np'); // Title shown in admin
            $this->method_description = __('NovaPoshta', 'woo-all-in-one-np'); // Description shown in admin
            $this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled
            $this->title = __('NovaPoshta', 'woo-all-in-one-np'); // This can be added as an setting but for this example its forced.
//            $this->supports           = array(
//                'shipping-zones',
//                'instance-settings',
//                'instance-settings-modal',
//            );

            $this->init_form_fields();
            $this->init();
        }

        public function init() {
            $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
            $this->cost = $this->get_option('cost');
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields() {
            include_once("NovaPoshtaApi2.php");

//            $this->instance_form_fields = array(
//                'title'      => array(
//                    'title'       => __( 'Title', 'woocommerce' ),
//                    'type'        => 'text',
//                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
//                    'default'     => __( 'Nova Poshta', 'woocommerce' ),
//                    'desc_tip'    => true,
//                ),
//                'tax_status' => array(
//                    'title'   => __( 'Tax status', 'woocommerce' ),
//                    'type'    => 'select',
//                    'class'   => 'wc-enhanced-select',
//                    'default' => 'taxable',
//                    'options' => array(
//                        'taxable' => __( 'Taxable', 'woocommerce' ),
//                        'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
//                    ),
//                ),
//                'cost'       => array(
//                    'title'       => __( 'Cost', 'woocommerce' ),
//                    'type'        => 'text',
//                    'placeholder' => '0',
//                    'description' => __( 'Optional cost for local pickup.', 'woocommerce' ),
//                    'default'     => '',
//                    'desc_tip'    => true,
//                ),
//            );

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woo-all-in-one-np'),
                    'type' => 'checkbox',
                    'label' => __('Enable NavaPostha', 'woo-all-in-one-np'),
                    'default' => 'yes'
                ),
                'description' => array(
                    'title' => __('Description', 'woo-all-in-one-np'),
                    'type' => 'textarea',
                    'desc_tip' => true,
                    'description' => __('This controls the description which the user sees during checkout.', 'woo-all-in-one-np'),
                    'default' => __("NovaPoshta Description", 'woo-all-in-one-np')
                ),
            );
        }

        public function calculate_shipping( $package = Array() ) {
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

    $methods[ ] = 'Woo_All_In_One_NP_SM';

    return $methods;

}

add_filter('woocommerce_shipping_methods', 'woionp_sm_add_nova_poshta');
