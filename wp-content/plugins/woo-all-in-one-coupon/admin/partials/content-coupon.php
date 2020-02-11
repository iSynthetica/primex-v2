<?php
// $coupon_id = Woo_All_In_One_Coupon_Model::generate_coupon();
// Woo_All_In_One_Coupon_Model::send_email_to_client($coupon_id, 'syntheticafreon@gmail.com');
?>
<h3 class="wp-heading-inline">
    <?php _e('Coupon settings', 'woo-all-in-one-coupon'); ?>
</h3>

<p><?php // echo $coupon_id; ?></p>

<?php echo do_shortcode('[wooaiocoupon_form]'); ?>