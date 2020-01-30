<?php
/**
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */

$where = array('ID' => $repair_id);
$repairs = Woo_All_In_One_Service_Model::get($where);
$repair_statuses = Woo_All_In_One_Service_Form::get_repairs_statuses();
$user_access_levels = Woo_All_In_One_Service_Helpers::get_user_access_levels();
$can_edit = false;
$can_delete = false;

if (in_array('edit', $user_access_levels)) {
    $can_edit = true;
}

if (in_array('delete', $user_access_levels)) {
    $can_edit = true;
    $can_delete = true;
}
if (empty($repairs)) {
    ?>
    <h3 class="wp-heading-inline">
        <?php _e('No Repair with ID', 'woo-all-in-one-service'); ?> <?php echo $repair_id; ?>
    </h3>

    <hr class="wp-header-end">
    <?php
} else {
    $repair = $repairs[0];
    ?>
    <h3 class="wp-heading-inline">
        <?php _e('Repair #', 'woo-all-in-one-service'); ?> <?php echo $repair['title']; ?>
    </h3>

    <hr class="wp-header-end">

    <div id="poststuff">
        <form id="repair_edit_form">
            <input type="hidden" name="repair_id" value="<?php echo $repair_id; ?>">
            <input type="hidden" name="repair_author" value="<?php echo $repair['author']; ?>">
            <input type="hidden" name="repair_title" value="<?php echo $repair['title']; ?>">
            <div id="service-container">
                <div id="primary-content">
                    <div id="customer-info" class="postbox">
                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Показать/скрыть панель: Краткое описание товара</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Customer Info', 'woo-all-in-one-service'); ?></span></h2>

                        <div class="inside">
                            <div class="repair-items">
                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Name', 'woocommerce' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $name = !empty($repair['name']) ? $repair['name'] : ''; ?>
                                        <input type="text" value="<?php echo $name ?>" name="repair_name" readonly>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Phone', 'woocommerce' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $phone = !empty($repair['phone']) ? $repair['phone'] : ''; ?>
                                        <input type="text" value="<?php echo $phone ?>" name="repair_phone" readonly>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Email address', 'woocommerce' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $email = !empty($repair['email']) ? $repair['email'] : ''; ?>
                                        <input type="text" value="<?php echo $email ?>" name="repair_email" readonly>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Date of request', 'woo-all-in-one-service' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php
                                        $created = !empty($repair['created']) ? $repair['created'] : '';

                                        if (!empty($created)) {
                                            $timestamp = strtotime($created);

                                            if (!$timestamp) {
                                                $created = '';
                                            } else {
                                                $created = gmdate( 'd-m-Y', $timestamp);
                                            }
                                        }
                                        ?>
                                        <input type="text" value="<?php echo $created ?>" name="repair_created_date" readonly>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Order date', 'woo-all-in-one-service' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php
                                        $order_date = !empty($repair['order_date']) ? $repair['order_date'] : '';

                                        if (!empty($order_date)) {
                                            $timestamp = strtotime($order_date);

                                            if (!$timestamp) {
                                                $order_date = '';
                                            } else {
                                                $order_date = gmdate( 'd-m-Y', $timestamp);
                                            }
                                        }
                                        ?>
                                        <input type="text" value="<?php echo $order_date ?>" name="repair_order_date" readonly>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'NP waybill number', 'woo-all-in-one-service' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $np_ttn = !empty($repair['np_ttn']) ? $repair['np_ttn'] : ''; ?>
                                        <input type="text" value="<?php echo $np_ttn ?>" name="repair_np_ttn" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="product-info" class="postbox">
                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Показать/скрыть панель: Краткое описание товара</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle"><span><?php _e('Product Info', 'woo-all-in-one-service'); ?></span></h2>

                        <div class="inside">

                            <div class="repair-items">
                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Product or model', 'woo-all-in-one-service' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $product_title = !empty($repair['product']) ? $repair['product'] : ''; ?>
                                        <input type="text" value="<?php echo $product_title ?>" name="repair_product"<?php echo !$can_edit ? ' readonly' : ''; ?>>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Serial number', 'woo-all-in-one-service' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $serial = !empty($repair['serial']) ? $repair['serial'] : ''; ?>
                                        <input type="text" value="<?php echo $serial ?>" name="repair_serial"<?php echo !$can_edit ? ' readonly' : ''; ?>>
                                    </div>
                                </div>

                                <div class="repair-item">
                                    <div class="repair-item-title">
                                        <?php _e( 'Condition', 'woo-all-in-one-service' ); ?>
                                    </div>

                                    <div class="repair-item-value">
                                        <?php $state = !empty($repair['state']) ? $repair['state'] : ''; ?>
                                        <input type="text" value="<?php echo $state ?>" name="repair_state"<?php echo !$can_edit ? ' readonly' : ''; ?>>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-title">
                                        <?php _e( 'Set included', 'woo-all-in-one-service' ); ?>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-value">
                                        <?php $set = !empty($repair['set']) ? $repair['set'] : ''; ?>
                                        <textarea name="repair_set"<?php echo !$can_edit ? ' readonly' : ''; ?>><?php echo $set ?></textarea>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-title">
                                        <?php _e( 'Declared malfunction', 'woo-all-in-one-service' ); ?>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-value">
                                        <?php $fault = !empty($repair['fault']) ? $repair['fault'] : ''; ?>
                                        <textarea name="repair_fault"<?php echo !$can_edit ? ' readonly' : ''; ?>><?php echo $fault ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="aside-content">
                    <div id="repair-info" class="postbox">
                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Показать/скрыть панель: Краткое описание товара</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle"><span><?php _e('Repair Info', 'woo-all-in-one-service'); ?></span></h2>

                        <div class="inside">
                            <div class="repair-items">
                                <div class="repair-item-full">
                                    <div class="repair-item-title">
                                        <?php _e( 'Repair status', 'woo-all-in-one-service' ); ?>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-value">
                                        <?php
                                        if ($can_edit) {
                                            ?>
                                            <select name="repair_status">
                                                <?php
                                                $status = ! empty( $repair['status'] ) ? $repair['status'] : '';
                                                foreach ($repair_statuses as $repair_status_key => $repair_status) {
                                                    ?>
                                                    <option value="<?php echo $repair_status_key ?>"<?php echo $repair_status_key === $status ? ' selected' : '' ?>><?php echo $repair_status ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                            <?php
                                        } else {
                                            $status = ! empty( $repair['status'] ) ? $repair['status'] : '';
                                            ?>
                                            <input type="hidden" name="repair_status" value="<?php echo $status ?>">
                                            <input type="text" name="repair_status" value="<?php echo $repair_statuses[$status] ?>" readonly>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-title">
                                        <?php _e( 'Repair result', 'woo-all-in-one-service' ); ?>
                                    </div>
                                </div>

                                <div class="repair-item-full">
                                    <div class="repair-item-value">
                                        <?php $result = !empty($repair['result']) ? $repair['result'] : ''; ?>
                                        <textarea name="repair_result" rows="6"<?php echo !$can_edit ? ' readonly' : ''; ?>><?php echo $result ?></textarea>
                                    </div>
                                </div>

                                <?php
                                if ($can_edit) {
                                    ?>
                                    <div class="repair-item-full">
                                        <div class="repair-item-action">
                                            <button id="repair_edit_submit" class="button button-primary" type="button">
                                                <?php _e( 'Update', 'woo-all-in-one-service' ); ?>
                                            </button>
                                            <?php
                                            if ($can_delete) {
                                                ?>
                                                <button class="button repair_delete_submit" type="button" data-id="<?php echo $repair_id; ?>" data-single="yes">
                                                    <?php _e( 'Delete', 'woo-all-in-one-service' ); ?>
                                                </button>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php
}
?>
