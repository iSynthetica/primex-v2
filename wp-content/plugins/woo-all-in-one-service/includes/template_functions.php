<?php
function wooaioservice_repairs_content() {
    $fields = Woo_All_In_One_Service_Form::get_form_fields();
    $fields_values = Woo_All_In_One_Service_Form::get_form_fields_values();
    $where = array('author' => get_current_user_id());
    $repairs = Woo_All_In_One_Service_Model::get($where);
    include (WOO_ALL_IN_ONE_SERVICE_PATH . 'woocommerce/repairs/repairs-list.php');
}

function wooaioservice_before_fields() {
    ?>
    <div class="row">
    <?php
}

function wooaioservice_after_fields() {
    ?>
    </div>
    <?php
}

function wooaioservice_email_data_show($data_id, $data_info) {
    $repair_statuses = Woo_All_In_One_Service_Form::get_repairs_statuses();
    ?>
    <p>
        <strong><?php echo $data_info['label'] ?>:</strong>
        <?php
        if ('textarea' !== $data_info['type']) {
            if ('status' === $data_id) {
                echo $repair_statuses[$data_info['value']];
            } else {
                echo $data_info['value'];
            }
        }
        ?>
    </p>
    <?php
    if ('textarea' === $data_info['type']) {
        ?>
        <p>
            <?php echo wpautop($data_info['value']); ?>
        </p>
        <?php
    }
}
