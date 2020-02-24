<?php
/**
 * Woocommerce NovaPoshta functions
 *
 * @package Hookah/Includes/WC
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add settings tab for Nova Poshta plugin
 *
 * @param $settings_tabs
 *
 * @return mixed
 */
function snth_wc_add_NP_setting_tab( $settings_tabs )
{
    $settings_tabs['snth_wc_np'] = __( 'Nova Poshta', 'snthwp' );

    return $settings_tabs;
}
add_filter( 'woocommerce_settings_tabs_array', 'snth_wc_add_NP_setting_tab', 50 );

/**
 *
 */
function snth_wc_NP_settings_tab()
{
    woocommerce_admin_fields( snth_wc_NP_get_settings() );
}
add_action( 'woocommerce_settings_tabs_snth_wc_np', 'snth_wc_NP_settings_tab' );

/**
 *
 */
function snth_wc_NP_update_settings() {
    woocommerce_update_options( snth_wc_NP_get_settings() );
}
add_action( 'woocommerce_update_options_snth_wc_np', 'snth_wc_NP_update_settings' );


/**
 * Get Settings fields for admin area
 * @return mixed|void
 */
function snth_wc_NP_get_settings()
{
    $city = snth_wc_NP_getCities();
    $area = snth_wc_NP_getAreas();
    $city_count = count( $city['data'] );
    $area_count = count( $area['data'] );
    $city_arr = array();
    $area_arr = array();
    $lang = "";

    // City List
    for ($i = 0; $i < $city_count; $i++) {
        if ($i == 0) {
            $j = __( 'Select City', 'snthwp' );
        } else {
            $j = $city['data'][$i]["Description" . $lang];
        }
        if ($i == 0) {
            $city_arr[''] = $j;
        } else {
            $city_arr[$j] = $j;
        }
    }

    // Arae List
    for ($i = 0; $i < $area_count; $i++) {
        if ($i == 0) {
            $j = __( 'Select Region', 'snthwp' );
        } else {
            $j = $area['data'][$i]["Description"];
        }
        if ($i == 0) {
            $area_arr[''] = $j;
        } else {
            $area_arr[$j] = $j;
        }
    }

    $settings = array(
        'section_title' => array(
            'name'     => __( 'Nova Poshta Settings', 'snthwp' ),
            'type'     => 'title',
            'desc'     => '',
            'id'       => 'snth_wc_np_section_title'
        ),
        'npApiKey' => array(
            'name' => __( 'Nova Poshta API key', 'snthwp' ),
            'type' => 'text',
            'desc_tip' => true,
            'desc' => __( 'API key for the NovaPoshta, is taken from your account <a href="https://my.novaposhta.ua/settings/index#apikeys">http://novaposhta.ua/apikeys</a>', 'snthwp' ),
            'id'   => 'snth_wc_np_api_key'
        ),
        'npAddressDep' => array(
            'name' => __( 'Departure address', 'snthwp' ),
            'type' => 'text',
            'desc_tip' => true,
            'desc' => __( 'Address, where the goods will be taken by local pickup.', 'snthwp' ),
            'id'   => 'snth_wc_np_address_dep'
        ),
        'npCityDep' => array(
            'name' => __('Departure city', 'snthwp'),
            'type' => 'select',
            'desc_tip' => true,
            'desc' => __('The city, where the goods will be sent.', 'snthwp'),
            'id'   => 'snth_wc_np_city_dep',
            'default' => __('Select City', 'snthwp'),
            'options' => $city_arr
        ),
        'npAreaDep' => array(
            'name' => __('Departure Area', 'snthwp'),
            'type' => 'select',
            'desc_tip' => true,
            'desc' => __('The area where the goods will be sent.', 'snthwp'),
            'id'   => 'snth_wc_np_area_dep',
            'default' => __('Select Region', 'snthwp'),
            'options' => $area_arr
        ),
        'calcShipingCost' => array(
            'title' => __('Summarize postage?', 'snthwp'),
            'type' => 'select',
            'desc_tip' => true,
            'id'   => 'snth_wc_np_calc_shipping',
            'desc' => __('Summarize postage to the total value of the goods?', 'snthwp'),
            'default' => __('No', 'snthwp'),
            'options' => array(
                '1' => __('Yes', 'snthwp'),
                '0' => __('No', 'snthwp'),
            ),
        ),
        'section_end' => array(
            'type' => 'sectionend',
            'id' => 'snth_wc_np_section_end'
        )
    );

    return apply_filters( 'wc_novaposhta_settings', $settings );
}

/**
 * Get Cities List from NP API
 *
 * @return mixed
 */
function snth_wc_NP_getCities()
{
    include_once("class-nova-poshta-api2.php");
    $apiKey = get_option('snth_wc_np_api_key');
    $np = new snthNovaPoshtaApi2( $apiKey );
    $city = $np->getCities();

    return $city;
}

/**
 * Get Areas form NP API
 *
 * @return mixed
 */
function snth_wc_NP_getAreas()
{
    include_once("class-nova-poshta-api2.php");
    $apiKey = get_option('snth_wc_np_api_key');
    $np = new snthNovaPoshtaApi2( $apiKey );
    $area = $np->getAreas();

    return $area;
}



/**
 * Nova Poshta Shipping methods
 *
 * Initial shipping methods for Nova Poshta delivery
 */
function snth_wc_NP_shipping_methods() {
    if (!class_exists('snth_wc_NovaPoshta')) {
        class snth_wc_NovaPoshta extends WC_Shipping_Method
        {
            public $serviceType = '';
            public $apiKey = '';
            public $np = null;
            public $npCityDep = '';
            public $npAreaDep = '';

            public function __construct()
            {
                include_once("class-nova-poshta-api2.php");
                $this->init();
                $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                $this->apiKey = get_option('snth_wc_np_api_key');
                $this->np = new snthNovaPoshtaApi2( $this->apiKey );
                $this->npCityDep = get_option('snth_wc_np_city_dep');
                $this->npAreaDep = get_option('snth_wc_np_area_dep');
            }

            /**
             * Init your settings
             *
             * @access public
             * @return void
             */
            function init() {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();

                // Save settings in admin if you have any defined
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }


            /**
             * This function is used to calculate the shipping cost.
             * Within this function we can check for weights,
             * dimensions and other parameters.
             *
             * @access public
             * @param mixed $package
             * @return void
             */
            public function calculate_shipping( $package = Array() ) {
                global $woocommerce;
                $biling_city = $woocommerce->customer->get_shipping_city();

//				if ($_POST['post_data']) {
//					$post_data = $_POST['post_data'];
//					SetCookie("post_data","");
//					SetCookie("post_data",$post_data);
//				}
//				else {
//					if (isset($_COOKIE['post_data']))
//						$post_data = $_COOKIE['post_data'];
//				}
//
//				$post_data = explode("&", $post_data);

//				$post_data = $_POST['post_data'];
//				parse_str( $post_data, $output );
//				$city_ref = $output['jcity_ref'];


//
//				foreach ($post_data as $word) {
//					$pos = strpos($word, 'jcity_ref');
//					if ($pos === false) {
//					} else {
//						$city_r = $word;
//						break;
//					}
//				}
//
//				$city_ref = explode("=", $city_r);
//				$city_ref = $city_ref[1];
//				$from = $this->settings['from'];
//				$weight = $woocommerce->cart->cart_contents_weight;
//
//				if (empty($weight) == true) {
//					$weight = $this->settings['weight'];
//				}
//
//				$cart = $woocommerce->cart->get_cart();
//				$defaultItemVolume = $this->settings['volume'];
//				$cost = 0;
//				$productVolume = 0;
//				$totalVolume = 0;
//
//				foreach ($cart as $itemId => $values) {
//					$product = $values['data'];
//
//					$height = floatval($product->height);
//					$length = floatval($product->length);
//					$width = floatval($product->width);
//					$weight = floatval($product->weight);
//
//					$height = wc_get_dimension( $height, 'cm');
//					$length = wc_get_dimension( $length, 'cm');
//					$width = wc_get_dimension( $width, 'cm');
//					$weight = wc_get_weight( $weight, 'kg' );
//
//					$quantity = intval($cart[$itemId]['quantity']);
//					$cost += floatval($product->price *  $quantity);
//
//					if($quantity > 1){
//						$productVolume += floatval(($height) * ($length * $quantity) * ($width)) / (4000);
//					}
//					else{
//						$productVolume += floatval(($height) * ($length) * ($width)) / (4000);
//					}
//
//					$weight = ($weight * $quantity);
//
//					if ($productVolume <= 0) {
//						$productVolume = $defaultItemVolume;
//					}
//
//					$totalVolume += floatval($productVolume);
//				}
//
//				$totalVolume = ( $totalVolume > $weight ) ? $totalVolume : $weight;
//
//				if ($biling_city) {
//					$sender_city = $this->np->getCity($this->npCityDep, $this->npAreaDep);
//					$sender_city_ref = $sender_city['data'][0]['Ref'];
//
//					if (!$sender_city_ref) {
//						$sender_city_ref = $sender_city['data'][0][0]['Ref'];
//					}
//
//					$recipient_city_ref = $city_ref;
//
//					$result = $this->np->getDocumentPrice($sender_city_ref, $recipient_city_ref, 'WarehouseWarehouse', $totalVolume, $cost, $productVolume);
//				}
//
//				$costs = $result["data"][0]["Cost"];

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

    if ( ! class_exists( 'snth_wc_LocalPickup' ) ) {
        class snth_wc_LocalPickup extends snth_wc_NovaPoshta {
            /**
             * Constructor for your shipping class
             */
            public function __construct() {
                $this->serviceType        = 'LocalPickup';
                $this->id                 = 'snth-pickup';
                $this->method_title       = __( 'Local Pickup', 'snthwp' );
                $this->method_description = __( 'Local Pickup Shipping Method', 'snthwp' );

                parent::__construct();

                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Local Pickup', 'snthwp' );
            }

            /**
             * Define settings field for this shipping
             * @return void
             */
            function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable', 'snthwp' ),
                        'type' => 'checkbox',
                        'desc_tip' => true,
                        'description' => __( 'Enable Local Pickup shipping.', 'snthwp' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'snthwp' ),
                        'type' => 'text',
                        'desc_tip' => true,
                        'description' => __( 'Title to be display on site', 'snthwp' ),
                        'default' => __( 'Local Pickup', 'snthwp' )
                    ),
                    'description' => array(
                        'title' => __('Description', 'snthwp'),
                        'type' => 'textarea',
                        'desc_tip' => true,
                        'description' => __('This controls the description which the user sees during checkout.', 'snthwp'),
                        'default' => __("Local Pickup Description", 'snthwp')
                    ),
                );
            }
        }
    }

    if ( ! class_exists( 'snth_wc_Other' ) ) {
        class snth_wc_Other extends snth_wc_NovaPoshta {
            /**
             * Constructor for your shipping class
             */
            public function __construct() {
                $this->serviceType        = 'OtherDelivery';
                $this->id                 = 'snth-other';
                $this->method_title       = __( 'Other Delivery', 'snthwp' );
                $this->method_description = __( 'Used mostly for foreign delivery', 'snthwp' );

                parent::__construct();

                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Other Delivery', 'snthwp' );
            }

            /**
             * Define settings field for this shipping
             * @return void
             */
            function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable', 'snthwp' ),
                        'type' => 'checkbox',
                        'desc_tip' => true,
                        'description' => __( 'Enable Other Delivery shipping.', 'snthwp' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'snthwp' ),
                        'type' => 'text',
                        'desc_tip' => true,
                        'description' => __( 'Title to be display on site', 'snthwp' ),
                        'default' => __( 'Other Delivery', 'snthwp' )
                    ),
                    'description' => array(
                        'title' => __('Description', 'snthwp'),
                        'type' => 'textarea',
                        'desc_tip' => true,
                        'description' => __('This controls the description which the user sees during checkout.', 'snthwp'),
                        'default' => __("Other Delivery Description", 'snthwp')
                    ),
                );
            }
        }
    }

    if ( ! class_exists( 'snth_wc_NovaPoshtaWarehouseWarehouse' ) ) {
        class snth_wc_NovaPoshtaWarehouseWarehouse extends snth_wc_NovaPoshta {
            /**
             * Constructor for your shipping class
             */
            public function __construct() {
                $this->serviceType        = 'WarehouseWarehouse';
                $this->id                 = 'snth-npww';
                $this->method_title       = __( 'NovaPoshta Warehouse', 'snthwp' );
                $this->method_description = __( 'NovaPoshta Shipping Method using Warehouse Warehouse Service Type', 'snthwp' );

                parent::__construct();

                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'NovaPoshta (Warehouse)', 'snthwp' );
            }

            /**
             * Define settings field for this shipping
             * @return void
             */
            function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable', 'snthwp' ),
                        'type' => 'checkbox',
                        'desc_tip' => true,
                        'description' => __( 'Enable Nova Poshta shipping.', 'snthwp' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'snthwp' ),
                        'type' => 'text',
                        'desc_tip' => true,
                        'description' => __( 'Title to be display on site', 'snthwp' ),
                        'default' => __( 'NovaPoshta (Warehouse - Warehouse)', 'snthwp' )
                    ),
                    'description' => array(
                        'title' => __('Description', 'snthwp'),
                        'type' => 'textarea',
                        'desc_tip' => true,
                        'description' => __('This controls the description which the user sees during checkout.', 'snthwp'),
                        'default' => __("NovaPoshta (Warehouse - Warehouse) Description", 'snthwp')
                    ),
                );
            }
        }
    }

    if ( ! class_exists( 'snth_wc_NovaPoshtaWarehouseDoors' ) ) {
        class snth_wc_NovaPoshtaWarehouseDoors extends snth_wc_NovaPoshta {
            /**
             * Constructor for your shipping class
             */
            public function __construct() {
                $this->serviceType        = 'WarehouseDoors';
                $this->id                 = 'snth-npwd';
                $this->method_title       = __( 'NovaPoshta Doors', 'snthwp' );
                $this->method_description = __( 'NovaPoshta Shipping Method using Warehouse Doors Service Type', 'snthwp' );

                parent::__construct();

                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'NovaPoshta (Doors)', 'snthwp' );
            }

            /**
             * Define settings field for this shipping
             * @return void
             */
            function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable', 'snthwp' ),
                        'type' => 'checkbox',
                        'desc_tip' => true,
                        'description' => __( 'Enable Nova Poshta shipping.', 'snthwp' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'snthwp' ),
                        'type' => 'text',
                        'desc_tip' => true,
                        'description' => __( 'Title to be display on site', 'snthwp' ),
                        'default' => __( 'NovaPoshta (Warehouse - Doors)', 'snthwp' )
                    ),
                    'description' => array(
                        'title' => __('Description', 'snthwp'),
                        'type' => 'textarea',
                        'desc_tip' => true,
                        'description' => __('This controls the description which the user sees during checkout.', 'snthwp'),
                        'default' => __("NovaPoshta (Warehouse - Doors) Description", 'snthwp')
                    ),
                );
            }
        }
    }
}
add_action( 'woocommerce_shipping_init', 'snth_wc_NP_shipping_methods' );

/**
 * Add Nova Poshta shipping methods
 *
 * @param $methods
 *
 * @return array
 */
function snth_wc_add_NP_shipping_method( $methods ) {
    $methods[] = 'snth_wc_LocalPickup';
    $methods[] = 'snth_wc_Other';
    $methods[] = 'snth_wc_NovaPoshtaWarehouseWarehouse';
    // $methods[] = 'snth_wc_NovaPoshtaWarehouseDoors';

    return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'snth_wc_add_NP_shipping_method' );


/**
 * Add fields for Nova Poshta delivery
 *
 * @param $fields
 *
 * @return mixed
 */
function snth_wc_NP_billing_fields( $fields )
{
    if( WC()->cart->needs_shipping() ) {
        $fields['jcountry_ref'] = array(
            'type'  =>  'hidden',
            //'type'  =>  'text',
            'label' => 'jcountry_ref',
        );
//        $fields['jarea_ref'] = array(
//            //'type'  =>  'hidden',
//            'type'  =>  'text',
//            'label' => 'jarea_ref',
//        );
//        $fields['jcity_ref'] = array(
//            //'type'  =>  'hidden',
//            'type'  =>  'text',
//            'label' => 'jcity_ref',
//        );
//        $fields['jaddress_ref'] = array(
//            //'type'  =>  'hidden',
//            'type'  =>  'text',
//            'label' => 'jaddress_ref',
//        );
        $fields['jshippingmethod_ref'] = array(
            'type'  =>  'hidden',
            //'type'  =>  'text',
            'label' => 'jshippingmethod_ref',
        );

    return $fields;
    }
}
add_filter('woocommerce_billing_fields', 'snth_wc_NP_billing_fields');

/**
 * Get Nova Poshta warehouses by city using Ajax
 */
function snth_wc_NP_ajax_get_warehouse_by_city()
{
    include_once("class-nova-poshta-api2.php");
    $apiKey = get_option('snth_wc_np_api_key');
    $np = new snthNovaPoshtaApi2( $apiKey );

    $city_ref = $_REQUEST['city_ref'];

    if( !isset( $city_ref ) ) {
        echo json_encode(array('status' => FALSE, 'city_ref' => '', 'data' => ''));
        die();
    }

    $warehouse = $np->getWarehouse($city_ref);

    /* header('Content-Type:application/json');*/
    echo json_encode(
        array(
            'status' => TRUE,
            'city_ref'=>$city_ref,
            'data' => array(
                'warehouse' => $warehouse,
            )
        )
    );

    die();

}
add_action('wp_ajax_np-api-get-warehouse-by-city', 'snth_wc_NP_ajax_get_warehouse_by_city');
add_action('wp_ajax_nopriv_np-api-get-warehouse-by-city', 'snth_wc_NP_ajax_get_warehouse_by_city');

/**
 * Get Nova Poshta warehouses by city using Ajax
 */
function snth_wc_NP_ajax_get_state_by_city()
{
    include_once("class-nova-poshta-api2.php");
    $apiKey = get_option('snth_wc_np_api_key');
    $np = new snthNovaPoshtaApi2( $apiKey );

    $area_ref = $_REQUEST['area_ref'];

    if( !isset( $area_ref ) ) {
        echo json_encode(array('status' => FALSE, 'city_ref' => '', 'data' => ''));
        die();
    }

    $area = $np->getAreas($area_ref);

    /* header('Content-Type:application/json');*/
    echo json_encode(
        array(
            'status' => TRUE,
            'area_ref'=>$area_ref,
            'data' => array(
                'area' => $area
            )
        )
    );

    die();

}
add_action('wp_ajax_np-api-get-state-by-city', 'snth_wc_NP_ajax_get_state_by_city');
add_action('wp_ajax_nopriv_np-api-get-state-by-city', 'snth_wc_NP_ajax_get_state_by_city');

/**
 * Get Nova Poshta warehouses by city using Ajax
 */
function snth_wc_NP_ajax_get_city()
{
    include_once("class-nova-poshta-api2.php");
    $apiKey = get_option('snth_wc_np_api_key');
    $np = new snthNovaPoshtaApi2( $apiKey );
    $city = $np->getCities();

    /* header('Content-Type:application/json');*/
    echo json_encode(
        array(
            'status' => TRUE,
            'data' => array(
                'city' => $city
            )
        )
    );

    die();

}
add_action('wp_ajax_np-api-get-city', 'snth_wc_NP_ajax_get_city');
add_action('wp_ajax_nopriv_np-api-get-city', 'snth_wc_NP_ajax_get_city');


require_once(SNTH_INCLUDES.'/wc-novaposhta-script.php');