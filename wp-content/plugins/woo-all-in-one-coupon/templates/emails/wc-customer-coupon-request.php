<?php
/**
 * @var $coupon_id
 * @var $email_heading
 * @var $email
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $coupon_id

$coupon = new WC_Coupon( $coupon_id );
$coupon_code = $coupon->get_code();

do_action( 'woocommerce_email_header', $email_heading, $email );

?>
    <p style="font-size: 19px;"><strong><?php echo __('Coupon Code', 'woo-all-in-one-coupon') .': ' . $coupon_code ?></strong></p>
<?php

do_action( 'woocommerce_email_footer', $email );
