<?php
/**
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */

$repairs = Woo_All_In_One_Service_Model::get();
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
?>
<h3 class="wp-heading-inline">
    <?php _e('Repairs List', 'woo-all-in-one-service'); ?>
</h3>

<hr class="wp-header-end">

<?php
if (empty($repairs)) {
    ?>
    <p></p>
    <?php
} else {
    ?>
    <table class="wp-list-table widefat fixed striped pages">
        <thead>
        <tr>
            <td class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All' ) ?></label>
                <input id="cb-select-all-1" type="checkbox" />
            </td>
            <th class="column-primary"><?php _e('#', 'woo-all-in-one-service'); ?></th>
            <th><?php _e( 'Product', 'woocommerce' ); ?></th>
            <th><?php _e( 'Name', 'woocommerce' ); ?></th>
            <th><?php _e( 'Phone', 'woocommerce' ); ?></th>
            <th><?php _e( 'Email address', 'woocommerce' ); ?></th>
            <th><?php _e('Status', 'woo-all-in-one-service'); ?></th>
            <?php
            if ($can_delete) {
                ?>
                <th><?php _e('Action', 'woo-all-in-one-service'); ?></th>
                <?php
            }
            ?>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php
        foreach ($repairs as $repair) {
            ?>
            <tr>
                <th class="check-column">
                    <input id="cb-select-<?php echo $repair['ID']; ?>" type="checkbox" value="<?php echo $repair['ID']; ?>">
                </th>

                <td class="column-primary has-row-actions">
                    <a href="?page=wooaioservice&tab=repairs&repair_id=<?php echo $repair['ID']; ?>">
                        <?php echo $repair['title'] ?>
                    </a>
                    <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                </td>

                <td data-colname="<?php _e( 'Product', 'woocommerce' ); ?>">
                    <?php echo $repair['product'] ?>
                </td>

                <td data-colname="<?php _e( 'Name', 'woocommerce' ); ?>">
                    <?php echo $repair['name'] ?>
                </td>

                <td data-colname="<?php _e( 'Phone', 'woocommerce' ); ?>">
                    <?php echo $repair['phone'] ?>
                </td>

                <td data-colname="<?php _e( 'Email address', 'woocommerce' ); ?>">
                    <?php echo $repair['email'] ?>
                </td>

                <td data-colname="<?php _e('Status', 'woo-all-in-one-service'); ?>">
                    <?php echo Woo_All_In_One_Service_Form::get_repairs_statuses()[$repair['status']] ?>
                </td>
                <?php
                if ($can_delete) {
                    ?>
                    <td data-colname="<?php _e('Action', 'woo-all-in-one-service'); ?>">
                        <button class="button repair_delete_submit" type="button" data-id="<?php echo $repair['ID']; ?>" data-single="no">
                            <?php _e( 'Delete', 'woo-all-in-one-service' ); ?>
                        </button>
                    </td>
                    <?php
                }
                ?>
            </tr>
            <?php
        }
        ?>

        </tbody>

        <tfoot>
        <tr>
            <td class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All' ) ?></label>
                <input id="cb-select-all-1" type="checkbox" />
            </td>
            <th class="column-primary"><?php _e('#', 'woo-all-in-one-service'); ?></th>
            <th><?php _e( 'Product', 'woocommerce' ); ?></th>
            <th><?php _e( 'Name', 'woocommerce' ); ?></th>
            <th><?php _e( 'Phone', 'woocommerce' ); ?></th>
            <th><?php _e( 'Email address', 'woocommerce' ); ?></th>
            <th><?php _e('Status', 'woo-all-in-one-service'); ?></th>
            <?php
            if ($can_delete) {
                ?>
                <th><?php _e('Action', 'woo-all-in-one-service'); ?></th>
                <?php
            }
            ?>
        </tr>
        </tfoot>
    </table>
    <?php
}
?>

