<?php
$fields = Woo_All_In_One_Service_Form::get_form_fields();
$fields_values = Woo_All_In_One_Service_Form::get_form_fields_values();
?>

<div id="wooaioservice_form_container">

    <form id="wooaioservice_form">
        <input type="hidden" name="repair_author" value="<?php echo get_current_user_id(); ?>">

    <?php do_action('wooaioservice_before_fields'); ?>

        <?php
        foreach ( $fields as $key => $field ) {
            $value = '';

            if (!empty($fields_values[$key]['value'])) {
                $value = $fields_values[$key]['value'];
            }

            woocommerce_form_field( $key, $field, $value );
        }
        ?>

        <button type="button" id="wooaioservice_submit" class="button">Submit</button>

    <?php do_action('wooaioservice_after_fields'); ?>
    </form>
</div>
