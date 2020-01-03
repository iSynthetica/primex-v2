<?php
$fields = Woo_All_In_One_Service_Form::get_form_fields();
$fields_values = Woo_All_In_One_Service_Form::get_form_fields_values();
$repairs = Woo_All_In_One_Service_Model::get();
?>

<div id="wooaioservice_messages_container"></div>

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

<div id="wooaioservice_list_container">
    <?php
    if (empty($repairs)) {
        ?>

        <?php
    } else {
        ?>
        <table id="wooaioservice-table" class="wooaioservice-table shop_table table table-hover table-sm shop_table_responsive">
            <?php
            foreach ($repairs as $repair) {
                ?>
                <tr>
                    <td>
                        <?php echo $repair['title']; ?>
                    </td>

                    <td>
                        <?php echo $repair['product']; ?>
                    </td>

                    <td>
                        <?php echo $repair['fault']; ?>
                    </td>

                    <td>
                        <?php
                        echo "<pre>";
                        print_r($repair);
                        echo "</pre>";
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
    ?>
</div>
