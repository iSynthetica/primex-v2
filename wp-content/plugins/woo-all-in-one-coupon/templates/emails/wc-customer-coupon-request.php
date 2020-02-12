<?php
/**
 * @var $coupon_id
 * @var $email_heading
 * @var $email
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$coupon = new WC_Coupon( $coupon_id );
$coupon_code = $coupon->get_code();
$coupon_rule = Woo_All_In_One_Coupon_Model::get_coupon_rule();

do_action( 'woocommerce_email_header', $email_heading, $email );

?>
    <p style="font-size: 19px;"><?php echo __('Coupon Code', 'woo-all-in-one-coupon') .': <strong>' . $coupon_code . '</strong>' ?></p>
<?php

if (!empty($coupon_rule['email_description'])) {
    ?>
    <?php echo wpautop($coupon_rule['email_description']); ?>
    <?php
}

do_action( 'woocommerce_email_footer', $email );
