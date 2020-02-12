<?php
// $coupon_id = Woo_All_In_One_Coupon_Model::generate_coupon();
// Woo_All_In_One_Coupon_Model::send_email_to_client($coupon_id, 'syntheticafreon@gmail.com');
?>

<?php include(dirname(__FILE__) . '/content-coupon-edit.php'); ?>

<p>
    <?php echo __('To show generate coupon form in your content copy and paste this shortcode'); ?>:
    <pre style="display: block;padding: 10px;background-color: #e6e6e6;border: 1px solid #c8c8c8;">[wooaiocoupon_form]</pre>
</p>

<p>
    <?php echo __('To show generate coupon form in your code copy and paste this script inside php block'); ?>:
    <pre style="display: block;padding: 10px;background-color: #e6e6e6;border: 1px solid #c8c8c8;">echo do_shortcode('[wooaiocoupon_form]')</pre>
</p>