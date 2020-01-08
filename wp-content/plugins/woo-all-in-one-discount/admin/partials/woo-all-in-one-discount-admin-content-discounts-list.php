<?php
/**
 * @var $discount_rules
 */

$discount_types = Woo_All_In_One_Discount_Rules::get_product_discounts_types();
?>

<h3 class="wp-heading-inline">
    <?php _e('Product Discount Rules List', 'woo-all-in-one-discount'); ?>
</h3>

<div id="wooaio-discount-create-action" class="wooaio-discount-create-closed">
    <button id="open-create-discount-rule" class="button button-primary" type="button"><?php _e('Create new discount rule', 'woo-all-in-one-discount'); ?></button>
    <button id="close-create-discount-rule" class="button" type="button"><?php _e('Cancel', 'woo-all-in-one-discount'); ?></button>
</div>

<div id="wooaio-discount-create-container" class="wooaio-discount-create-closed">
    <form id="wooaio-discount-create-form" style="width: 100%;max-width:600px;">
        <div class="wooaio-discount-item">
            <div>
                <label for="discount_title"><?php _e('Title', 'woo-all-in-one-discount'); ?></label>
            </div>
            <div>
                <input type="text" id="discount_title" name="discount_title">
            </div>

        </div>

        <div class="wooaio-discount-item">
            <div>
                <label for="discount_description"><?php _e('Description', 'woo-all-in-one-discount'); ?></label>
            </div>
            <div>
                <textarea name="discount_description" id="discount_description" rows="5"></textarea>
            </div>
        </div>

        <div class="wooaio-discount-item">
            <div>
                <label for="discount_type"><?php _e('Type', 'woo-all-in-one-discount'); ?></label>
            </div>
            <div>
                <select name="discount_type" id="discount_type">
                    <?php
                    foreach ($discount_types as $discount_type_slug => $discount_type_label) {
                        ?>
                        <option value="<?php echo $discount_type_slug; ?>"><?php echo $discount_type_label; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="wooaio-discount-item">
            <div></div>
            <div>
                <button id="create-discount-submit" class="button" type="button"><?php _e('Create', 'woo-all-in-one-discount'); ?></button>
            </div>
        </div>
    </form>
</div>

<div id="wooaio-discount-list">
    <?php
    if (empty($discount_rules)) {
        ?>
        <p><?php _e('There is no product discount rules. Create new one clicking "Create new discount rule" button above.', 'woo-all-in-one-discount'); ?></p>
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
                <th class="column-primary"><?php _e('ID', 'woo-all-in-one-discount'); ?></th>
                <th><?php _e('Description', 'woo-all-in-one-discount'); ?></th>
                <th><?php _e('Status', 'woo-all-in-one-discount'); ?></th>
                <th><?php _e('Action', 'woo-all-in-one-discount'); ?></th>
            </tr>
            </thead>

            <tbody id="the-list">
            <?php
            foreach ($discount_rules as $discount_rule_id => $discount_rule) {
                ?>
                <tr>
                    <th class="check-column">
                        <input id="cb-select-<?php echo $discount_rule_id; ?>" type="checkbox" value="<?php echo $discount_rule_id; ?>">
                    </th>

                    <td class="column-primary has-row-actions">
                        <a href="?page=wooaiodiscount&tab=discounts&discount_id=<?php echo $discount_rule_id; ?>">
                            <?php echo $discount_rule['title'] ?>
                        </a>
                        <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                    </td>

                    <td data-colname="<?php _e('Description', 'woo-all-in-one-discount'); ?>">
                        <?php echo wpautop($discount_rule['description']) ?>
                    </td>

                    <td data-colname="<?php _e('Status', 'woo-all-in-one-discount'); ?>">
                        <?php echo Woo_All_In_One_Discount_Rules::get_product_discounts_statuses()[$discount_rule['status']] ?>
                    </td>

                    <td data-colname="<?php _e('Action', 'woo-all-in-one-discount'); ?>">
                        <button
                                id="delete-discount-<?php echo $discount_rule_id; ?>"
                                class="button button-small delete-discount-rule"
                                type="button"
                                data-id="<?php echo $discount_rule_id; ?>"
                                data-single="no"
                                data-confirm="<?php _e('Are you sure you want to delete this discount rule?', 'woo-all-in-one-discount'); ?>"
                        ><?php _e('Delete', 'woo-all-in-one-discount'); ?></button>
                    </td>
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
                <th class="column-primary"><?php _e('ID', 'woo-all-in-one-discount'); ?></th>
                <th><?php _e('Description', 'woo-all-in-one-discount'); ?></th>
                <th><?php _e('Status', 'woo-all-in-one-discount'); ?></th>
                <th><?php _e('Action', 'woo-all-in-one-discount'); ?></th>
            </tr>
            </tfoot>
        </table>
        <?php
    }
    ?>
</div>
