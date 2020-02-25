<?php

function woionp_sm_settings_init() {
    class Woo_All_In_One_NP_SM_Settings extends WC_Shipping_Method {
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'novaposhta_general';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __('NovaPoshta Settings', 'woo-all-in-one-np');
            $this->method_description = __('NovaPoshta shipping method Settings', 'woo-all-in-one-np'); // Description shown in admin

            $this->init();

            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->title      = $this->get_option( 'title' );
        }

        public function init_form_fields() {
            $city = Woo_All_In_One_NP_API::get_cities();
            $area = Woo_All_In_One_NP_API::get_areas();

            if (!empty($city)) {
                $city_arr = array(
                    '' => __( 'Select city', 'woo-all-in-one-np' ),
                );

                foreach ($city as $item) {
                    $city_arr[$item['Ref']] = $item['Description'];
                }
            } else {
                $city_arr = array(
                    '' => __( 'No city', 'woo-all-in-one-np' ),
                );
            }

            $this->form_fields = array(
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
                    'label' => __('Enable NavaPostha', 'woo-all-in-one-np'),
                    'default' => 'yes'
                ),
                'CityRecipient' => array(
                    'title' => __('Departure city', 'novaposhta'),
                    'type' => 'select',
                    'desc_tip' => true,
                    'description' => __('The city, where the goods will be sent.', 'novaposhta'),
                    'default' => __('Select a city', 'novaposhta'),
                    'options' => $city_arr
                ),
//                'AreaRecipient' => array(
//                    'title' => __('Departure Area', 'novaposhta'),
//                    'type' => 'select',
//                    'desc_tip' => true,
//                    'description' => __('The area where the goods will be sent.', 'novaposhta'),
//                    'default' => __('Choose a region', 'novaposhta'),
//                    'options' => $area_arr
//                ),
                'np_API_key' => array(
                    'title' => __('NavaPostha API_key', 'woo-all-in-one-np'),
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => __('API key for the NovaPoshta, is taken from your account <a href="https://my.novaposhta.ua/settings/index#apikeys">http://novaposhta.ua/apikeys</a>', 'woo-all-in-one-np'),
                    'default' => ''
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

function woionp_sm_add_nova_poshta_settings($methods)
{

    $methods['novaposhta_settings'] = 'Woo_All_In_One_NP_SM_Settings';

    return $methods;

}

add_filter('woocommerce_shipping_methods', 'woionp_sm_add_nova_poshta_settings');
