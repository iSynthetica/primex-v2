<?php
/**
 * @var $repair
 * @var $email_heading
 * @var $email
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$email_array = Woo_All_In_One_Service_Form::get_email_repair_data_by_sections($repair);
$repair_statuses = Woo_All_In_One_Service_Form::get_repairs_statuses();

do_action( 'woocommerce_email_header', $email_heading, $email );

foreach ($email_array as $section_id => $section_data) {
    if ('service-info' === $section_id) {
        continue;
    } else {
        ?>
        <p style="font-size: 19px;"><strong><?php echo $section_data['label'] ?></strong></p>
        <?php
        foreach ($section_data['data'] as $data_id => $data_info) {
            if (!empty($data_info['value'])) {
                wooaioservice_email_data_show($data_id, $data_info);
            }
        }
    }
}

do_action( 'woocommerce_email_footer', $email );
