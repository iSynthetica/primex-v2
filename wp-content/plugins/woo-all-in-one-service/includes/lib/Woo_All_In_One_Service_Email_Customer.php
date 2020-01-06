<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

class Woo_All_In_One_Service_Email_Customer extends WC_Email {
    public function __construct() {
        $this->id = 'wc_customer_review_request';
        $this->title = __( 'Repair Request to Customer', 'woo-all-in-one-service' );
        $this->description = __( 'An email sent to the customer for Repair Request.', 'woo-all-in-one-service' );
        $this->customer_email = true;
        $this->heading     = __( 'Repair request #', 'woo-all-in-one-service' );
        $this->subject     = sprintf( _x( '[%s] - Repair Request', 'default email subject for cancelled emails sent to the customer', 'woo-all-in-one-service' ), '{blogname}' );

        $this->email_type = $this->get_option( 'email_type', 'multipart' );
        $this->template_html  = 'emails/wc-customer-repair-request.php';
        $this->template_plain = 'emails/plain/wc-customer-repair-request.php';
        $this->template_base = untrailingslashit( WOO_ALL_IN_ONE_SERVICE_PATH ) . '/templates/';

        parent::__construct();
    }

    /**
     * @param $order_id
     */
    public function trigger( $repair_id ) {
        $this->setup_locale();

        $where = array('ID' => $repair_id);
        $repairs = Woo_All_In_One_Service_Model::get($where);
        $this->object = $repairs[0];
        $this->heading = $this->heading . ' ' . $this->object['title'];

        $this->recipient = $this->object['email'];

        $send = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        if ($send && empty($email)) { // If review request sent and not test mode
            do_action('wooaioservice_status_changed_email_sent', $repair_id);
        }

        $this->restore_locale();
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'repair'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'			=> $this
        ), '', $this->template_base );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'repair'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'			=> $this
        ), '', $this->template_base );
    }
}