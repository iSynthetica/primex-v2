<?php
/**
 * @var $repair
 * @var $email_heading
 */
$repair_id = $repair['ID'];
$email_array = Woo_All_In_One_Service_Form::get_email_repair_data_by_sections($repair);
$repair_statuses = Woo_All_In_One_Service_Form::get_repairs_statuses();

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="  . PHP_EOL;
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo PHP_EOL . "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL  . PHP_EOL;

foreach ($email_array as $section_id => $section_data) {
    if ('service-info' === $section_id) {
        continue;
    } else {
        echo $section_data['label'] . PHP_EOL;

        foreach ($section_data['data'] as $data_id => $data_info) {
            if (!empty($data_info['value'])) {
                if ('status' === $data_id) {
                    echo $data_info['label'] .': ' . $repair_statuses[$data_info['value']] . PHP_EOL;
                } else {
                    echo $data_info['label'] .': ' . esc_html( wp_strip_all_tags( $data_info['value'] ) ) . PHP_EOL;
                }
            }
        }
        echo PHP_EOL . PHP_EOL;
    }
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
