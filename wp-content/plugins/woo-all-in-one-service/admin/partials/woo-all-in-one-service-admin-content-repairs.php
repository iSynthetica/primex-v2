<?php
/**
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */

$repair_id = !empty($_GET['repair_id']) ? sanitize_text_field($_GET['repair_id']) : false;

if ($repair_id) {
    include(dirname(__FILE__) . '/woo-all-in-one-service-admin-content-repairs-edit.php');
} else {
    include(dirname(__FILE__) . '/woo-all-in-one-service-admin-content-repairs-list.php');
}
?>
