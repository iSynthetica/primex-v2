<?php
add_action('plugins_loaded', 'woocommerce_novaposhta_np_init', 0);
function woocommerce_novaposhta_np_init(){
  if(!class_exists('WC_Payment_Gateway')) return;

  class WC_novaposhta_np_Payu extends WC_Payment_Gateway{
    public function __construct(){
      $this -> id = 'novatoshta_np';
      $this -> medthod_title = 'Новая Почта (наложенный платеж)';
      $this -> has_fields = false;

      $this -> init_form_fields();
      $this -> init_settings();
      $this -> title = $this->settings['title'];
      $this -> description = $this->settings['description'];
      $this -> liveurl = 'https://secure.NovaPoshta.in/_payment';

      $this -> msg['message'] = "";
      $this -> msg['class'] = "";

      add_action('init', array(&$this, 'check_NovaPoshta_response'));
      if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
      } else {
        add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
      }
      add_action('woocommerce_receipt_NovaPoshta', array(&$this, 'receipt_page'));
    }
    function init_form_fields(){

     $this -> form_fields = array(
      'enabled' => array(
        'title' => __('Enable/Disable', 'novaposhta'),
        'type' => 'checkbox',
        'label' => __('Enable NovaPoshta Payment Module.', 'novaposhta'),
        'default' => 'yes'),
      'title' => array(
        'title' => __('Title:', 'novaposhta'),
        'type'=> 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'novaposhta'),
        'default' => __('Novaposhta (cash on delivery)', 'novaposhta')),
      'description' => array(
        'title' => __('Description:', 'novaposhta'),
        'type' => 'textarea',
        'description' => __('This controls the description which the user sees during checkout.', 'novaposhta'),
        'default' => __('Pay securely by Credit or Debit card or internet banking through NovaPoshta Secure Servers.', 'novaposhta'),
      ),
     );
   }

   public function admin_options(){
    echo '<h3>'.__('NovaPoshta Payment Gateway', 'novaposhta').'</h3>';
    echo '<p>'.__('NovaPoshta is most popular payment gateway for online shopping in Ukraine').'</p>';
    echo '<table class="form-table">';
        // Generate the HTML For the settings form.
    $this -> generate_settings_html();
    echo '</table>';

  }

    /**
     *  There are no payment fields for NovaPoshta, but we want to show the description if set.
     **/
    function payment_fields(){
      if($this -> description) echo wpautop(wptexturize($this -> description));
    }
    /**
     * Receipt Page
     **/
    function receipt_page($order){
      echo '<p>'.__('Thank you for your order, please click the button below to pay with NovaPoshta.', 'novaposhta').'</p>';

    }

    /**
     * Process the payment and return the result
     **/
    function process_payment($order_id){

      $order = wc_get_order( $order_id );

    // Mark as processing (payment won't be taken until delivery)
      $order->update_status( 'processing', __( 'Payment to be made upon delivery.', 'woocommerce' ) );

    // Reduce stock levels
      $order->reduce_order_stock();

    // Return thankyou redirect
      return array(
        'result'  => 'success',
        'redirect'  => $this->get_return_url( $order )
        );
    }

    public function class_woo_cart_has_virtual_product() {

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

    /**
     * Check for valid NovaPoshta server callback
     **/
    function check_NovaPoshta_response(){
      global $woocommerce;
      if(isset($_REQUEST['txnid']) && isset($_REQUEST['mihpayid'])){
        $order_id_time = $_REQUEST['txnid'];
        $order_id = explode('_', $_REQUEST['txnid']);
        $order_id = (int)$order_id[0];
        if($order_id != ''){
          try{
            $order = new WC_Order($order_id);
            $merchant_id = $_REQUEST['key'];
            $amount = $_REQUEST['Amount'];
            $hash = $_REQUEST['hash'];

            $status = $_REQUEST['status'];
            $productinfo = "Order $order_id";
            echo $hash;
            echo "{$this->salt}|$status|||||||||||{$order->billing_email}|{$order->billing_first_name}|$productinfo|{$order->order_total}|$order_id_time|{$this->merchant_id}";
            $checkhash = hash('sha512', "{$this->salt}|$status|||||||||||{$order->billing_email}|{$order->billing_first_name}|$productinfo|{$order->order_total}|$order_id_time|{$this->merchant_id}");
            $transauthorised = false;
            if($order -> status !=='completed'){
              if($hash == $checkhash)
              {

                $status = strtolower($status);

                if($status=="success"){
                  $transauthorised = true;
                  $this -> msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.";
                  $this -> msg['class'] = 'woocommerce_message';
                  if($order -> status == 'processing'){

                  }else{
                    $order -> payment_complete();
                    $order -> add_order_note('NovaPoshta payment successful<br/>Unnique Id from NovaPoshta: '.$_REQUEST['mihpayid']);
                    $order -> add_order_note($this->msg['message']);
                    $woocommerce -> cart -> empty_cart();
                  }
                }else if($status=="pending"){
                  $this -> msg['message'] = "Thank you for shopping with us. Right now your payment staus is pending, We will keep you posted regarding the status of your order through e-mail";
                  $this -> msg['class'] = 'woocommerce_message woocommerce_message_info';
                  $order -> add_order_note('NovaPoshta payment status is pending<br/>Unnique Id from NovaPoshta: '.$_REQUEST['mihpayid']);
                  $order -> add_order_note($this->msg['message']);
                  $order -> update_status('on-hold');
                  $woocommerce -> cart -> empty_cart();
                }
                else{
                  $this -> msg['class'] = 'woocommerce_error';
                  $this -> msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
                  $order -> add_order_note('Transaction Declined: '.$_REQUEST['Error']);
                                //Here you need to put in the routines for a failed
                                //transaction such as sending an email to customer
                                //setting database status etc etc
                }
              }else{
                $this -> msg['class'] = 'error';
                $this -> msg['message'] = "Security Error. Illegal access detected";

                            //Here you need to simply ignore this and dont need
                            //to perform any operation in this condition
              }
              if($transauthorised==false){
                $order -> update_status('failed');
                $order -> add_order_note('Failed');
                $order -> add_order_note($this->msg['message']);
              }
              add_action('the_content', array(&$this, 'showMessage'));
            }}catch(Exception $e){
                        // $errorOccurred = true;
              $msg = "Error";
            }

          }



        }

      }

      function showMessage($content){
        return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
      }
     // get all pages
      function get_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
          $prefix = '';
            // show indented child pages?
          if ($indent) {
            $has_parent = $page->post_parent;
            while($has_parent) {
              $prefix .=  ' - ';
              $next_page = get_page($has_parent);
              $has_parent = $next_page->post_parent;
            }
          }
            // add to page list array array
          $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
      }
    }
   /**
     * Add the Gateway to WooCommerce
     **/
   function woocommerce_add_novaposhta_np_gateway($methods) {
     $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $methods );

     if (is_admin()) {
       $methods[] = 'WC_novaposhta_np_Payu'; }

     elseif (WC()->cart->needs_shipping())
     $methods[] = 'WC_novaposhta_np_Payu';

   return $methods;
 }

 add_filter('woocommerce_payment_gateways', 'woocommerce_add_novaposhta_np_gateway' );

// add text on top of the checkout page

 //add_action( 'woocommerce_thankyou', 'add_thankyou');

 function add_thankyou() {

   $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
   $chosen_shipping = $chosen_methods[0];
   if($chosen_shipping == 'novaposhta') {
     if (get_option('np_res') == "") $otdelenie_np = "Отделение не выбрано";
     else
       $otdelenie_np = get_option('np_res');
     echo '<p><strong>' . __('Warhouse NovaPostha','novaposhta') . ':</strong> ' . $otdelenie_np . '</p>';
   }

}


//add_action( 'woocommerce_email_before_order_table','add_order_email_instructions', 10, 4 );
function add_order_email_instructions( $order, $sent_to_admin, $plain_text, $email ) {
  if(WC()->session) {
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    $chosen_shipping_methods = $chosen_shipping_methods[0];
    if ($chosen_shipping_methods == 'novaposhta') {
      echo get_option('np_res');
    }
  }
}


//add_filter( 'woocommerce_shortcode_products_query', 'woocommerce_shortcode_products_orderby' );

function woocommerce_shortcode_products_orderby( $args ) {

  $standard_array = array('menu_order','title','date','rand','id');

  if( isset( $args['orderby'] ) && !in_array( $args['orderby'], $standard_array ) ) {
    $args['meta_key'] = $args['orderby'];
    $args['orderby']  = 'meta_value_num';
  }

  return $args;
}

  add_action('woocommerce_checkout_update_order_meta',  'updateOrderMeta');
  function updateOrderMeta($orderId)
  {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $chosen_shipping = $chosen_methods[0];
    if($chosen_shipping == 'novaposhta') {
      if (get_option('np_res') == "") $otdelenie_np = "Отделение не выбрано";
      else
        $otdelenie_np = get_option('np_res');
      update_post_meta($orderId, '_shipping_address_1', $otdelenie_np);
    }


  }


  add_filter( 'woocommerce_billing_fields', 'wc_optional_billing_fields', 10, 1 );
  function wc_optional_billing_fields( $address_fields ) {
    $address_fields['billing_address_1']['required'] = false;
    $address_fields['billing_address_2']['required'] = false;
    $address_fields['billing_postcode']['required'] = false;
    $address_fields['billing_state']['required'] = false;
    return $address_fields;
  }
  add_filter( 'woocommerce_shipping_fields', 'wc_optional_shipping_fields', 10, 1 );
  function wc_optional_shipping_fields( $address_fields ) {
    $address_fields['shipping_address_1']['required'] = false;
    $address_fields['shipping_address_2']['required'] = false;
    $address_fields['shipping_postcode']['required'] = false;
    $address_fields['shipping_state']['required'] = false;
    return $address_fields;
  }
}

