<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

class Woo_All_In_One_Coupon_Email_Customer extends WC_Email {
    public function __construct() {
        $this->id = 'wc_customer_coupon_request';
        $this->title = __( 'Generated coupon to customer', 'woo-all-in-one-coupon' );
        $this->description = __( 'An email sent to the customer for Repair Request.', 'woo-all-in-one-coupon' );
        $this->customer_email = true;
        $this->heading     = __( 'Your discount coupon', 'woo-all-in-one-coupon' );
        $this->subject     = sprintf( _x( '[%s] - Your discount coupon', 'default email subject for emails sent to the customer', 'woo-all-in-one-coupon' ), '{blogname}' );

        $this->email_type = $this->get_option( 'email_type', 'multipart' );
        $this->template_html  = 'emails/wc-customer-coupon-request.php';
        $this->template_plain = 'emails/plain/wc-customer-coupon-request.php';
        $this->template_base = untrailingslashit( WAIO_COUPON_PATH ) . '/templates/';

        parent::__construct();
    }

    /**
     * @param $coupon_id
     * @param $email
     *
     * @return bool
     */
    public function trigger( $coupon_id, $email ) {
        $this->setup_locale();
        $this->object = $coupon_id;
        $this->heading = $this->heading . ' ' . $this->object['title'];

        $this->recipient = $email;

        $send = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        if ($send) { // If review request sent and not test mode
            do_action('wooaiocoupon_coupon_email_sent', $coupon_id);
        }

        $this->restore_locale();

        return $send;
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'coupon_id'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'			=> $this
        ), '', $this->template_base );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'coupon_id'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'			=> $this
        ), '', $this->template_base );
    }
}
