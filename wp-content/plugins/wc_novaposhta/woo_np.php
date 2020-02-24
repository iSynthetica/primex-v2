<?php

/*
Plugin Name:NovaPoshta&WooCommerce
Plugin URI: http://pfy.in.ua/liqpay/
Description: Шлюз доставки для Woocommerce
Author: M.I. Simkin
Version: 1.6
*/


include_once('np_class.php');
include_once('novaposhta_np.php');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    function nova_poshta_init()
    {

        if (!class_exists('WC_Nova_Poshta')) {

            class WC_Nova_Poshta extends WC_Shipping_Method
            {

                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */

                public function __construct()
                {

                    $this->id = 'novaposhta'; // Id for your shipping method. Should be uunique.

                    $this->method_title = __('NovaPoshta', 'novaposhta'); // Title shown in admin

                    $this->method_description = __('NovaPoshta', 'novaposhta'); // Description shown in admin

                    $this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled

                    $this->title = __('NovaPoshta', 'novaposhta'); // This can be added as an setting but for this example its forced.
                    $this->init_form_fields();

                    $this->init();

                }


                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */

                function init()
                {
                    // Load the settings API
                    //$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
                    $this->cost = $this->get_option('cost');
                    // Save settings in admin if you have any defined
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

                }

                /**
                 * Initialise Gateway Settings Form Fields
                 */
                function init_form_fields()
                {
                    $l = "Description";

                    include_once("NovaPoshtaApi2.php");
                    $NP_settings = get_option('woocommerce_novaposhta_settings');
                    $np_API_key = $NP_settings['np_API_key'];
                    $lang = get_lang();
                    $np = new NovaPoshtaApi2($np_API_key);
                    $city = $np->getCities();
                    $area = $np->getAreas();
                    $city_cont = count($city['data']);
                    $area_cont = count($area['data']);
                    $city_arr = array();
                    $area_arr = array();
                    $rr = "Ru";
                    for ($i = 0; $i < $city_cont; $i++) {
                        if ($i == 0) {
                            $j = "Выберите Город";
                        } else {
                            $j = $city['data'][$i]["Description" . $lang];
                        }
                        if ($i == 0) {
                            $city_arr[''] = $j;
                        } else {
                            $city_arr[$j] = $j;
                        }
                    }

                    for ($i = 0; $i < $area_cont; $i++) {
                        if ($i == 0) {
                            $j = "Выберите Область";
                        } else {
                            $j = $area['data'][$i]["Description"];
                        }
                        if ($i == 0) {
                            $area_arr[''] = $j;
                        } else {
                            $area_arr[$j] = $j;
                        }
                    }

                    $this->form_fields = array(
                        'enabled' => array(
                            'title' => __('Enable/Disable', 'novaposhta'),
                            'type' => 'checkbox',
                            'label' => __('Enable NavaPostha', 'novaposhta'),
                            'default' => 'yes'
                        ),
                        'np_API_key' => array(
                            'title' => __('np_API_key', 'novaposhta'),
                            'type' => 'text',
                            'desc_tip' => true,
                            'description' => __('API key for the NovaPoshta, is taken from your account <a href="https://my.novaposhta.ua/settings/index#apikeys">http://novaposhta.ua/apikeys</a>', 'novaposhta'),
                            'default' => ''
                        ),
                        'np_current_Class' => array(
                            'title' => __('You Class', 'novaposhta'),
                            'type' => 'text',
                            'desc_tip' => true,
                            'description' => __('You Class', 'novaposhta'),
                            'default' => ''
                        ),
                        'CityRecipient' => array(
                            'title' => __('Departure city', 'novaposhta'),
                            'type' => 'select',
                            'desc_tip' => true,
                            'description' => __('The city, where the goods will be sent.', 'novaposhta'),
                            'default' => __('Select a city', 'novaposhta'),
                            'options' => $city_arr
                        ),
                        'AreaRecipient' => array(
                            'title' => __('Departure Area', 'novaposhta'),
                            'type' => 'select',
                            'desc_tip' => true,
                            'description' => __('The area where the goods will be sent.', 'novaposhta'),
                            'default' => __('Choose a region', 'novaposhta'),
                            'options' => $area_arr
                        ),
                        'FreeShiping' => array(
                            'title' => __('Free shipping?', 'novaposhta'),
                            'type' => 'select',
                            'desc_tip' => true,
                            'description' => __('On/Off Free Shipping', 'novaposhta'),
                            'default' => __('No', 'novaposhta'),
                            'options' => array(
                                __('1', 'novaposhta') => __('Yes', 'novaposhta'),
                                __('0', 'novaposhta') => __('No', 'novaposhta'),
                            ),
                        ),
                        'CalcShipingCost' => array(
                            'title' => __('Summarize postage?', 'novaposhta'),
                            'type' => 'select',
                            'desc_tip' => true,
                            'description' => __('Summarize postage to the total value of the goods?', 'novaposhta'),
                            'default' => __('No', 'novaposhta'),
                            'options' => array(
                                __('1', 'novaposhta') => __('Yes', 'novaposhta'),
                                __('0', 'novaposhta') => __('No', 'novaposhta'),
                            ),
                        ),
                        'request_np_field' => array(
                            'title' => __('Field Branch NP choice necessary?', 'novaposhta'),
                            'type' => 'select',
                            'desc_tip' => true,
                            'description' => __('Do I have to fill the field, select the number of department NovaPoshta?', 'novaposhta'),
                            'default' => __('No', 'novaposhta'),
                            'options' => array(
                                __('1', 'novaposhta') => __('Yes', 'novaposhta'),
                                __('0', 'novaposhta') => __('No', 'novaposhta'),
                            ),
                        ),
                        'description' => array(
                            'title' => __('Description', 'novaposhta'),
                            'type' => 'textarea',
                            'desc_tip' => true,
                            'description' => __('This controls the description which the user sees during checkout.', 'novaposhta'),
                            'default' => __("NovaPoshta Description", 'novaposhta')
                        ),
                        'lang' => array(
                            'title' => __('Language', 'novaposhta'),
                            'type' => 'select',
                            'options' => array(
                                __('RU', 'novaposhta') => __('Ru', 'novaposhta'),
                                __('UA', 'novaposhta') => __('Ua', 'novaposhta'),
                            ),


                        )
                    );
                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping($package = Array())
                {
                    global $woocommerce;
                    $NP_settings = get_option('woocommerce_novaposhta_settings');
                    //$costs = $NP_settings['cost'];
                    $np_API_key = $NP_settings['np_API_key'];
                    $cityrecipient = $NP_settings['CityRecipient'];
                    $arearecipient = $NP_settings['AreaRecipient'];
                    include_once("NovaPoshtaApi2.php");
                    $np = new NovaPoshtaApi2($np_API_key);
                    // Получение кода города по названию города и области
                    $biling_city = $woocommerce->customer->get_shipping_city();

                    if (!empty($_POST['post_data'])) {
                        $post_data = $_POST['post_data'];
                        SetCookie("post_data","");
                        SetCookie("post_data",$post_data);
                    }
                    else {
                        if (isset($_COOKIE['post_data']))
                            $post_data = $_COOKIE['post_data'];
                    }

                    $post_data = explode("&", $post_data);
                    foreach ($post_data as $word) {
                        $pos = strpos($word, 'jcity_ref');
                        if ($pos === false) {
                        } else {
                            $city_r = $word;
                            break;
                        }
                    }
                    $city_ref = explode("=", $city_r);
                    $city_ref = $city_ref[1];


                    // set default city FROM
                    $from = $this->settings['from'];
                    // get weight for cart
                    $weight = $woocommerce->cart->cart_contents_weight;
                    // if empty, use default from settings
                    if (empty($weight) == true) {
                        $weight = $this->settings['weight'];
                    }
                    $cart = $woocommerce->cart->get_cart();

                    $defaultItemVolume = $this->settings['volume'];
                    $cost = 0;
                    $productVolume = 0;
                    $totalVolume = 0;
                    foreach ($cart as $itemId => $values) {

                        $product = $values['data'];

                        $height = floatval($product->height);
                        $length = floatval($product->length);
                        $width = floatval($product->width);
                        $weight = floatval($product->weight);



                        $height = wc_get_dimension( $height, 'cm');
                        $length = wc_get_dimension( $length, 'cm');
                        $width = wc_get_dimension( $width, 'cm');
                        $weight = wc_get_weight( $weight, 'kg' );

                        $quantity = intval($cart[$itemId]['quantity']);
                        $cost += floatval($product->price *  $quantity);


                        if($quantity > 1){
                            $productVolume += floatval(($height) * ($length * $quantity) * ($width)) / (4000);
                        }
                        else{
                            $productVolume += floatval(($height) * ($length) * ($width)) / (4000);
                        }

                        $weight = ($weight*$quantity);
                        // if volume is 0, use default value
                        if ($productVolume <= 0) {
                            $productVolume = $defaultItemVolume;
                        }

                        $totalVolume += floatval($productVolume);


                    }
                    $totalVolume = ($totalVolume>$weight)?$totalVolume:$weight;

                    //var_dump($order->payment_method_title);

                    if ($biling_city) {
                        $sender_city = $np->getCity($cityrecipient, $arearecipient);

                        //if ($sender_city['success'] == false)
                        // $sender_city = $np->getArea($cityrecipient,$arearecipient);
                        $sender_city_ref = $sender_city['data'][0]['Ref'];
                        if (!$sender_city_ref)
                            $sender_city_ref = $sender_city['data'][0][0]['Ref'];
                        //
                        $recipient_city_ref = $city_ref;

                        if(is_plugin_active('woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php')){
                            $currency = get_woocommerce_currency();
                            $cost = apply_filters('wc_aelia_cs_convert', $cost,
                                $currency,"UAH");
                        }

                        $result = $np->getDocumentPrice($sender_city_ref, $recipient_city_ref, 'WarehouseWarehouse', $totalVolume, $cost, $productVolume);


                    }

                    if(is_plugin_active('woocommerce-currency-switcher/index.php')) {
                        global $WOOCS;


                        $course = $WOOCS->get_currencies()[$WOOCS->current_currency]['rate'];

                        if($WOOCS->current_currency == "USD"){
                            $costs = $result["data"][0]["Cost"]/$WOOCS->get_currencies()["UAH"]['rate'];
                        }
                        else{
                            $costs = $result["data"][0]["Cost"]/$course;
                        }
                    }
                    elseif(is_plugin_active('woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php')){
                        $costs = apply_filters('wc_aelia_cs_convert', $result["data"][0]["Cost"],
                            "UAH",
                            get_option('woocommerce_currency'));
                    }
                    else{
                        $costs = $result["data"][0]["Cost"];
                    }


                    $freeshiping = $NP_settings['FreeShiping'];
                    $CalcShipingCost = $NP_settings['CalcShipingCost'];


                    if (($CalcShipingCost == "0") || ($freeshiping == "1")) {
                        $costs_text = '';
                        if($costs) $costs_text =  " ($costs ".get_woocommerce_currency_symbol( $currency ).")";
                        $rate = array(
                            'id' => $this->id,
                            'label' => $this->title . $costs_text,
                            'cost' => 0,
                            'calc_tax' => 'per_item'
                        );
                    } else {
                        $rate = array(
                            'id' => $this->id,
                            'label' => $this->title,
                            'cost' => $costs,
                            'calc_tax' => 'per_item'
                        );
                    }

                    $this->add_rate($rate);

                }


            }


        }
    }

    add_action('woocommerce_shipping_init', 'nova_poshta_init');
    function add_nova_poshta($methods)
    {

        $methods[ ] = 'WC_Nova_Poshta';

        return $methods;

    }

    add_filter('woocommerce_shipping_methods', 'add_nova_poshta');


    function np_add_style()
    {
        wp_enqueue_style('np_style', plugins_url('style_np.css', __FILE__));
        wp_enqueue_style('chosen_style', plugins_url('include/chosen/chosen.css', __FILE__));
        wp_enqueue_script('checkout_js', plugins_url('checkout.js', __FILE__), array('jquery'));
        wp_enqueue_script('chosen_js', plugins_url('include/chosen/chosen.jquery.min.js', __FILE__), array('jquery'));
    }

    add_action('init', 'np_add_style');
}


add_action('plugins_loaded', 'np_load_languages');

function np_load_languages()
{
    load_plugin_textdomain('novaposhta', false, dirname(plugin_basename(__FILE__)) . '/language');
}

?>