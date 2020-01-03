<?php
/**
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */

$where = array('ID' => $repair_id);
$repairs = Woo_All_In_One_Service_Model::get($where);
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

    <?php
}
?>
