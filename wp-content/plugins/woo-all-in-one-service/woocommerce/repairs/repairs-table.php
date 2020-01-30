<?php
/**
 *
 */

$repair_statuses = Woo_All_In_One_Service_Form::get_repairs_statuses();
?>

<div id="wooaioservice_list_container">
    <?php
    if (empty($repairs)) {
        ?>

        <?php
    } else {
        ?>
        <table id="wooaioservice-table" class="wooaioservice-table shop_table table table-hover table-sm shop_table_responsive">
            <thead>
                <tr>
                    <th class="repair-title"><?php _e('#', 'woo-all-in-one-service'); ?></th>
                    <th class="repair-title"><?php _e( 'Product', 'woocommerce' ); ?></th>
                    <th class="repair-title"><?php _e( 'Date of request', 'woo-all-in-one-service' ); ?></th>
                    <th class="repair-title"><?php _e( 'Declared malfunction', 'woo-all-in-one-service' ); ?></th>
                    <th class="repair-title"><?php _e( 'Repair result', 'woo-all-in-one-service' ); ?></th>
                    <th class="repair-title"><?php _e('Status', 'woo-all-in-one-service'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($repairs as $repair) {
                    ?>
                    <tr>
                        <td data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                            <?php echo $repair['title']; ?>
                        </td>

                        <td>
                            <?php echo $repair['product']; ?>
                        </td>

                        <td>
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
                            <?php echo $created; ?>
                        </td>

                        <td>
                            <?php echo $repair['fault']; ?>
                        </td>

                        <td>
                            <?php $result = !empty($repair['result']) ? $repair['result'] : ''; ?>
                            <?php echo $result; ?>
                        </td>

                        <td>
                            <?php
                            $status = ! empty( $repair['status'] ) ? $repair['status'] : '';
                            echo $repair_statuses[$status];
//                            echo "<pre>";
//                            print_r($repair);
//                            echo "</pre>";
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }
    ?>
</div>
