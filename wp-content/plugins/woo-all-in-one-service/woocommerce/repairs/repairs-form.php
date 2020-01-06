<?php
/**
 *
 */
?>
<div id="wooaioservice_container">
    <div id="wooaioservice_form_container_control" class="wooaioservice-form-closed">
        <div class="row">
            <div class="col-12">
                <button type="button" id="wooaioservice_create" class="button">
                    <?php echo __( 'Create New', 'woo-all-in-one-service' ) ?>
                </button>

                <button type="button" id="wooaioservice_cancel" class="button">
                    <?php echo __( 'Cancel', 'woo-all-in-one-service' ) ?>
                </button>
            </div>
        </div>
    </div>

    <div id="wooaioservice_form_container" class="wooaioservice-form-closed">
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

            <div class="col-12">
                <button type="button" id="wooaioservice_submit" class="button">
                    <?php echo __( 'Submit', 'woo-all-in-one-service' ) ?>
                </button>
            </div>

            <?php do_action('wooaioservice_after_fields'); ?>
        </form>
    </div>
</div>
