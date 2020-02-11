<?php
class Woo_All_In_One_Coupon_Custom_Email {
    public function __construct() {
        add_action( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    }

    public function register_email( $emails ) {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Woo_All_In_One_Coupon_Email_Customer.php';

        $emails['Woo_All_In_One_Service_Email_Customer'] = new Woo_All_In_One_Coupon_Email_Customer();

        return $emails;
    }
}

new Woo_All_In_One_Coupon_Custom_Email();