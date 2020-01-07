<?php
add_action( 'admin_menu', 'wooaioie_admin_menu', 100 );

function wooaioie_admin_menu() {
    if (function_exists('run_woo_all_in_one') && current_user_can( 'manage_options' )) {
        add_submenu_page(
            'wooaio',
            __('Import', 'woo-all-in-one-ie'),
            __('Product Import', 'woo-all-in-one-ie'),
            'manage_options',
            'wooaioie-page',
            'wooaioie_admin_page',
            10
        );

    } else {
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

}

function wooaioie_admin_page() {
    if (!empty($_GET['subpage']) && in_array($_GET['subpage'], array(
            'import_terms',
            'export_terms',
            'export_products',
            'import_products',
            'export_users',
            'import_users',
            'tables',
            'export_attributes',
            'import_attributes'
        ) )
    ) {
        include WOOAIOIE_PATH . 'admin/partials/'.$_GET['subpage'].'.php';
    } else {
        include WOOAIOIE_PATH . 'admin/partials/start.php';
    }
}

add_action('admin_enqueue_scripts', 'wooaioie_admin_enqueue_scripts');

function wooaioie_admin_enqueue_scripts() {
    wp_enqueue_script('wooaioie', WOOAIOIE_URL . '/admin/assets/script.js', array('jquery'), '1.0.0', true);
}

add_action('wp_ajax_wooaioie_import_products', 'wooaioie_import_products');

function wooaioie_import_products() {
    $products = !empty($_POST['products']) ? $_POST['products'] : false;

    if (empty($products)) {
        $response = array('success' => 0, 'error' => 1, 'message' => __('No products', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    $products = unserialize(stripslashes($products));

    if (empty($products)) {
        $response = array('success' => 0, 'error' => 1, 'message' => __('Wrong format', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    $result = Wooaioie_Background_Create_Product::bg_process($products);

    $count = count($products);

    $response = array('success' => 1, 'error' => 0, 'message' => $count . __(' Started', 'more-better-reviews-for-woocommerce'));

    echo json_encode($response);
    wp_die();
}