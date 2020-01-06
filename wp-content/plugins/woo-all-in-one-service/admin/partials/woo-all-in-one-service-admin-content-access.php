<?php
/**
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */

global $wp_roles;

$access_settings = get_option('wooaioservice_access_settings', false);
$access_settings_roles = array();

if (!empty($access_settings)) {
    $access_settings_roles = array_keys($access_settings);
}
?>

<?php
// var_dump($wp_roles->role_names);
?>
<h3 class="wp-heading-inline">
    <?php _e('Access Rules List', 'woo-all-in-one-service'); ?>
</h3>

<div id="wooaioservice-access-create-action" class="wooaioservice-access-create-closed">
    <button id="open-create-access-rule" class="button button-primary" type="button"><?php _e('Create new access rule', 'woo-all-in-one-service'); ?></button>
    <button id="close-create-access-rule" class="button" type="button"><?php _e('Cancel', 'woo-all-in-one-service'); ?></button>
</div>

<div id="wooaioservice-access-create" class="wooaioservice-access-create-closed">
    <form id="wooaioservice-access-create-form">
        <div>
            <div style="display: inline-block; margin-right: 30px;">
                <select name="wooaioservice-access-role" id="">
                    <option value=""><?php _e('-- select user role --', 'woo-all-in-one-service'); ?></option>
                    <?php
                    foreach ($wp_roles->role_names as $role_slug => $role_label) {
                        if ('administrator' === $role_slug || 'subscriber' === $role_slug || 'customer' === $role_slug || in_array($role_slug, $access_settings_roles)) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo $role_slug ?>"><?php echo $role_label ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="margin-right: 10px;">
                    <?php echo __('Select access level', 'woo-all-in-one-service'); ?>
                </label>
                <label for="wooaioservice-access-read" style="margin-right: 10px;">
                    <?php _e('Read', 'woo-all-in-one-service'); ?>
                    <input id="wooaioservice-access-read" type="radio" value="read" name="wooaioservice_access_level">
                </label>

                <label for="wooaioservice-access-edit" style="margin-right: 10px;">
                    <?php _e('Read', 'woo-all-in-one-service'); ?>/<?php _e('Edit', 'woo-all-in-one-service'); ?>
                    <input id="wooaioservice-access-edit" type="radio" value="edit" name="wooaioservice_access_level">
                </label>

                <label for="wooaioservice-access-delete" style="margin-right: 10px;">
                    <?php _e('Read', 'woo-all-in-one-service'); ?>/<?php _e('Edit', 'woo-all-in-one-service'); ?>/<?php _e('Delete', 'woo-all-in-one-service'); ?>
                    <input id="wooaioservice-access-delete" type="radio" value="delete" name="wooaioservice_access_level">
                </label>
            </div>

            <div style="display: inline-block;vertical-align: middle;">
                <button id="create-access-rule" class="button button-primary" type="button"><?php _e('Create rule', 'woo-all-in-one-service'); ?></button>
            </div>
        </div>
    </form>
</div>

<div id="wooaioservice-access-rules">
    <?php
    if (!empty($access_settings)) {
        ?>
        <table class="wp-list-table widefat fixed striped pages">
            <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All' ) ?></label>
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <th class="column-primary"><?php _e('User Role', 'woo-all-in-one-service'); ?></th>
                <th><?php _e( 'Access Level', 'woo-all-in-one-service' ); ?></th>
                <th><?php _e( 'Action', 'woo-all-in-one-service' ); ?></th>
            </tr>
            </thead>

            <tbody>
                <?php
                foreach ($access_settings as $role_access_slug => $role_access) {
                    ?>
                    <tr>
                        <th class="check-column">
                            <input id="cb-select-<?php echo $role_access_slug; ?>" type="checkbox" value="<?php echo $role_access_slug; ?>">
                        </th>

                        <td class="column-primary has-row-actions">
                            <?php echo $wp_roles->role_names[$role_access_slug]; ?>
                            <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                        </td>

                        <td data-colname="<?php _e( 'Access Level', 'woo-all-in-one-service' ); ?>">
                            <form id="wooaioservice-access-create-form-<?php echo $role_access_slug; ?>">
                                <label for="wooaioservice-access-read-<?php echo $role_access_slug; ?>" style="margin-right: 10px;">
                                    <?php _e('Read', 'woo-all-in-one-service'); ?>
                                    <input id="wooaioservice-access-read-<?php echo $role_access_slug; ?>" type="radio" value="read" name="wooaioservice_access_level"<?php echo 'read' === $role_access['rule'] ? ' checked' : ''; ?>>
                                </label>

                                <label for="wooaioservice-access-edit-<?php echo $role_access_slug; ?>" style="margin-right: 10px;">
                                    <?php _e('Read', 'woo-all-in-one-service'); ?>/<?php _e('Edit', 'woo-all-in-one-service'); ?>
                                    <input id="wooaioservice-access-edit-<?php echo $role_access_slug; ?>" type="radio" value="edit" name="wooaioservice_access_level"<?php echo 'edit' === $role_access['rule'] ? ' checked' : ''; ?>>
                                </label>

                                <label for="wooaioservice-access-delete-<?php echo $role_access_slug; ?>" style="margin-right: 10px;">
                                    <?php _e('Read', 'woo-all-in-one-service'); ?>/<?php _e('Edit', 'woo-all-in-one-service'); ?>/<?php _e('Delete', 'woo-all-in-one-service'); ?>
                                    <input id="wooaioservice-access-delete-<?php echo $role_access_slug; ?>" type="radio" value="delete" name="wooaioservice_access_level"<?php echo 'delete' === $role_access['rule'] ? ' checked' : ''; ?>>
                                </label>
                            </form>
                        </td>

                        <td data-colname="<?php _e( 'Action', 'woo-all-in-one-service' ); ?>">
                            <button class="button button-primary access-update-rule" type="button" data-role="<?php echo $role_access_slug; ?>"><?php _e('Update rule', 'woo-all-in-one-service'); ?></button>
                            <button class="button access-delete-rule" type="button" data-role="<?php echo $role_access_slug; ?>"><?php _e('Delete rule', 'woo-all-in-one-service'); ?></button>
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
                <th class="column-primary"><?php _e('User Role', 'woo-all-in-one-service'); ?></th>
                <th><?php _e( 'Access Level', 'woo-all-in-one-service' ); ?></th>
                <th><?php _e( 'Action', 'woo-all-in-one-service' ); ?></th>
            </tr>
            </tfoot>
        </table>
        <?php
    } else {
        ?>
        <p>
            <strong>
                <?php _e('Create new access rule by clicking a button above', 'woo-all-in-one-service'); ?>
            </strong>
        </p>
        <?php
    }
    ?>
</div>
