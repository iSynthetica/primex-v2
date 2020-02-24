<?php

/**
* Add the field to order checkbox
**/

$res = '';
global $warhouse_np;
$warehouse_np = array();

include_once ("NovaPoshtaApi2.php");
$NP_settings = get_option( 'woocommerce_novaposhta_settings');
$np_API_key = $NP_settings['np_API_key'];

$np = new NovaPoshtaApi2($np_API_key);

function get_lang () {
  $NP_settings = get_option( 'woocommerce_novaposhta_settings');
  $lang = $NP_settings['lang'];
  $lang = ($lang == "UA")? "" : "Ru";
  return $lang;
}


add_action('woocommerce_checkout_order_processed', 'update_pmeta_np');
function update_pmeta_np($order_id) {

  update_post_meta( $order_id, "_shipping_address_1", $_REQUEST["billing_np_number"]);
  update_post_meta( $order_id, "_shipping_city_ref", $_REQUEST["jcity_ref"]);
  update_post_meta( $order_id, "_shipping_wh_ref", $_REQUEST["jwarhouse_ref"]);

}


//Remove (Free) label on cart page for "Shipping and Handling" if cost is $0
function sv_change_cart_shipping_free_label( $free_label ) {
  $free_label =  str_replace( "(Free)", " ", $free_label );
  $free_label =  str_replace( "(Бесплатно)", " ", $free_label );

  return $free_label;
}
add_filter( 'woocommerce_cart_shipping_method_full_label' , 'sv_change_cart_shipping_free_label' );

///add_action( 'woocommerce_admin_order_data_after_billing_address', 'add_otdelenie_field_display_admin_order_meta', 10, 1 );

function add_otdelenie_field_display_admin_order_meta($order){

 if (!get_post_meta( $order->id, "shipping_method_1" ) )
  $otdelenie_np = "Отделение не выбрано";
else {
      $otdelenie_np = get_post_meta( $order->id, "shipping_method_1" );//get_option( 'np_res');
      $otdelenie_np = $otdelenie_np[0];
    }

    echo '<p><strong>'.__('Warhouse NovaPoshta','novaposhta').':</strong> ' . $otdelenie_np . '</p>';

}



/*  function action_woocommerce_checkout_process( $wccs_custom_checkout_field_process ) {
    global $woocommerce;

    $bool = wc_cart_has_virtual_product();

    if (!$bool) {
     if ( (!$_REQUEST['shipping_method']) || (empty($_REQUEST['shipping_method']) || ($_REQUEST['shipping_method'] === "") || (is_null($_REQUEST['shipping_method'])) && ($_REQUEST['billing_city'])) ) {
      wc_add_notice( '<strong>Отделение Новой Почты </strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
    }
  }


};





add_action( 'woocommerce_checkout_process', 'action_woocommerce_checkout_process', 10, 1 ); */

function wc_cart_has_virtual_product() {

  global $woocommerce;

  // By default, no virtual product
  $has_virtual_products = false;

  // Default virtual products number
  $virtual_products = 0;

  // Get all products in cart
  $products = $woocommerce->cart->get_cart();

  // Loop through cart products
  foreach( $products as $product ) {

    // Get product ID and '_virtual' post meta
    $product_id = $product['product_id'];
    $is_virtual = get_post_meta( $product_id, '_virtual', true );

    // Update $has_virtual_product if product is virtual
    if( $is_virtual == 'yes' )
      $virtual_products += 1;
  }

  if( count($products) == $virtual_products )
    $has_virtual_products = true;

  return $has_virtual_products;
}




// add the action
//include_once plugins_url('../woocommerce/includes/abstracts/abstract-wc-product.php', __FILE__);



// Add a new checkout field
function add_new_billing_fields($fields) {
  $NP_settings = get_option( 'woocommerce_novaposhta_settings');
  $request_np_field = $NP_settings['request_np_field'];
  if ($request_np_field == "0")  $request_np = false;
  else $request_np = true;
 if(WC()->cart->needs_shipping()){
  $fields['billing_np_number'] = array(
      'label' => 'Отделение новой почты (№)',
      'placeholder' => 'Отделение новой почты (№)',
      'required'    => $request_np,

  );
  $fields['jcity_ref'] = array(
      'label' => 'REF',
      'placeholder' => 'REF',

  );}

  return $fields;
}

add_filter('woocommerce_billing_fields', 'add_new_billing_fields');



add_filter('woocommerce_locate_template', 'woo_adon_plugin_template', 1, 3);

function woo_adon_plugin_template($template, $template_name, $template_path) {
  global $woocommerce;
  $_template = $template;
  if (!$template_path)
    $template_path = $woocommerce->template_url;

  $plugin_path = untrailingslashit(plugin_dir_path(__FILE__)) . '/template/woocommerce/';

  // Look within passed path within the theme - this is priority
  $template = locate_template(
      array(
          $template_path . $template_name,
          $template_name
      )
  );

  if (!$template && file_exists($plugin_path . $template_name))
    $template = $plugin_path . $template_name;

  if (!$template)
    $template = $_template;

  return $template;
}


add_filter("woocommerce_checkout_fields", "order_fields");

function order_fields($fields) {

  $order = array(
      "billing_first_name",
      "billing_last_name",
      "billing_phone",
      "billing_email",
      "billing_postcode",
      "billing_company",
      "billing_country",
      "billing_state",
      "billing_city",
      "billing_np_number",
      "billing_address_1",
      "billing_address_2",
      "jcity_ref",
      "jwarhouse_ref"
  );
  foreach ($order as $field) {
    if ((($field == 'billing_np_number') || ($field == 'jcity_ref')) && (WC()->cart->needs_shipping()))
      $ordered_fields[$field] = $fields["billing"][$field];
    elseif (($field != 'billing_np_number') && ($field != 'jcity_ref'))
      $ordered_fields[$field] = $fields["billing"][$field];
  }

  $fields["billing"] = $ordered_fields;
  return $fields;
}

add_action('wp_ajax_nopriv_np_api_get_cities_by_area', 'getWarhouseByCityRefAjax');
add_action('wp_ajax_np_api_get_cities_by_area', 'getWarhouseByCityRefAjax');


function getWarhouseByCityRefAjax(){

    $NP_settings = get_option( 'woocommerce_novaposhta_settings');
    $np_API_key = $NP_settings['np_API_key'];
  //file_put_contents('1ref.txt', print_r($_REQUEST,1));
    $np = new NovaPoshtaApi2($np_API_key,'ru');

    $city_ref = $_REQUEST['city_ref'];
  if (isset($city_ref)) {

    $result = $np->getWarehouse($city_ref);

   /* header('Content-Type:application/json');*/
    echo json_encode(array('status' => TRUE, 'city_ref'=>$city_ref, 'data' => $result));

    die();
  }
  else {
      echo json_encode(array('status' => FALSE, 'city_ref' => '', 'data' => ''));
      die();
  }

}


/*add_action('wp_ajax_nopriv_np_select_warhouse', 'Select_NP_warhouse');
add_action('wp_ajax_np_select_warhouse', 'Select_NP_warhouse');
function Select_NP_warhouse(){
    $warhouse = $_REQUEST['warhouse'];
    update_option( 'np_res', $warhouse );
  echo json_encode(array("result"=>'success'));
die();
}*/

function my_custom_available_payment_gateways( $gateways) {
  global $woocommerce;
  if ( is_admin() && ! defined( 'DOING_AJAX' ) )
    return;
  if(isset($_REQUEST['wc-ajax']) && $_REQUEST['wc-ajax'] == 'checkout')
  {  return $gateways;
    return;}

    $chosen_shipping_rates = WC()->session->get('chosen_shipping_methods');
    if ('novaposhta' !== $chosen_shipping_rates[0]) {
      unset($gateways['novatoshta_np']);
      ?>
      <script>
        jQuery('#billing_np_number_field').css('display', 'none');
        jQuery('#billing_postcode_field').css('display', 'block');
        jQuery('#billing_company_field').css('display', 'block');
        jQuery('#billing_address_1_field').css('display', 'block');
        jQuery('#billing_address_2_field').css('display', 'block');
        jQuery('#shipping_postcode_field').css('display', 'block');
        jQuery('#shipping_company_field').css('display', 'block');
        jQuery('#billing_state_field').css('display', 'block');
        jQuery('#shipping_address_1_field').css('display', 'block');
        jQuery('#shipping_address_2_field').css('display', 'block');
        jQuery('#billing_np_number_field').find('.chosen-single span').text(jQuery("#billing_np_number option:first").text());
        jQuery("#billing_np_number option:first").val(jQuery("#billing_np_number option:first").text());
      </script><?php
    }
    else {
      ?>
      <script>
        jQuery('#billing_np_number_field').css('display', 'block');
        jQuery('#billing_postcode_field').css('display', 'none');
        jQuery('#billing_company_field').css('display', 'none');
        jQuery('#billing_address_1_field').css('display', 'none');
        jQuery('#billing_address_2_field').css('display', 'none');
        jQuery('#billing_state_field').css('display', 'none');
        jQuery('#shipping_postcode_field').css('display', 'none');
        jQuery('#shipping_company_field').css('display', 'none');
        jQuery('#shipping_address_1_field').css('display', 'none');
        jQuery('#shipping_address_2_field').css('display', 'none');
        jQuery('#billing_city_field').css('clear', 'both');
        jQuery("#billing_np_number option:first").val('');
      </script>
      <?php
    }
    return $gateways;

}
add_filter( 'woocommerce_available_payment_gateways', 'my_custom_available_payment_gateways' );

?>