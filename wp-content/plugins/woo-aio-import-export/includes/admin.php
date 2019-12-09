<?php
add_action( 'admin_menu', 'wooaioie_admin_menu' );

function wooaioie_admin_menu() {
    add_menu_page(
        __('Import', 'woo-all-in-one-ie'),
        __('Product Import', 'woo-all-in-one-ie'),
        'manage_options',
        'wooaioie-page',
        'wooaioie_admin_page',
        'dashicons-tickets-alt',
        6
    );
}

function wooaioie_admin_page() {
    ?>
    <h1><?php echo __('Import', 'woo-all-in-one-ie'); ?></h1>
    <?php
    include WOOAIOIE_PATH . 'admin/partials/import.php';
}