<?php
/**
 * @var $repair
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$repair_id = $repair['ID'];
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
    <?php printf( __( 'Review request for order #%d.', 'more-better-reviews-for-woocommerce' ), $repair_id ); ?>
</p>

<?php echo $repair_id; ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>