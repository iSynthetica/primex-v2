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
