<?php
// Warning: explode() expects parameter 2 to be string, array given in /var/www/tsvitokha/primex2.0/public_html/wp-content/plugins/woocommerce/includes/data-stores/class-wc-coupon-data-store-cpt.php on line 123

class Woo_All_In_One_Coupon_Model {
    public static function generate_coupon() {
        global $wpdb;
        $exists = true;

        while($exists) {
            $code = Woo_All_In_One_Coupon_Model::generate_unique_code();
            $sql = "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1;";
            $sql_prepared = $wpdb->prepare( $sql , $code );
            $coupon_id = $wpdb->get_var( $sql_prepared );

            if ( empty( $coupon_id ) ) {
                $exists = false;
            }
        }

        $coupon = array(
            'post_title' => $code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon'
        );



        $new_coupon_id = wp_insert_post( $coupon );
        $data = Woo_All_In_One_Coupon_Model::get_coupon_settings();


        // Write the $data values into postmeta table
        foreach ($data as $key => $value) {
            update_post_meta( $new_coupon_id, $key, $value );
        }

        return $new_coupon_id;
    }

    public static function get_coupon_settings() {
        $now = time();
        $days = 10;
        $expire_timestamp = $now + (60 * 60 * 24 * $days);
        $expire = date('Y-m-d', $expire_timestamp);

        $data = array(
            'discount_type'              => 'fixed_cart',
            'coupon_amount'              => 100, // value
            'individual_use'             => 'no',
            'product_ids'                => '',
            'exclude_product_ids'        => '',
            'usage_limit'                => '',
            'usage_limit_per_user'       => '1',
            'limit_usage_to_x_items'     => '',
            'usage_count'                => '',
            'expiry_date'                => $expire, // YYYY-MM-DD
            'free_shipping'              => 'no',
            'product_categories'         => array(),
            'exclude_product_categories' => array(),
            'exclude_sale_items'         => 'no',
            'minimum_amount'             => '',
            'maximum_amount'             => '',
            'customer_email'             => array()
        );

        return $data;
    }

    public static function generate_unique_code($n = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public static function send_email_to_client($coupon_id, $recipient) {
        $mailer = WC()->mailer();
        $email = $mailer->emails['Woo_All_In_One_Coupon_Email_Customer'];
        $email->trigger( $coupon_id, $recipient );
    }
}