<?php
function wooaioc_enqueue_scripts() {
    if (!empty($_GET['page']) && 'wooaiocatalogue' === $_GET['page']) {

        if (!function_exists('run_woo_all_in_one')) {
            wp_enqueue_style( 'WooAIOCatalogue-grid', plugin_dir_url( __FILE__ ) . 'css/wooaio-flexboxgrid.css', array(), '0.6.3', 'all' );
        }
        wp_enqueue_style( 'WooAIOCatalogue', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '0.0.1', 'all' );

        wp_enqueue_script( 'WooAIOCatalogue', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ), '0.0.1', true );
    }
}

add_action( 'admin_enqueue_scripts', 'wooaioc_enqueue_scripts' );

function wooaioc_admin_menu() {
    if (function_exists('run_woo_all_in_one') && current_user_can( 'manage_options' )) {
        add_submenu_page(
            'wooaio',
            __('Catalogue', 'woo-all-in-one-catalogue'),
            __('Product Catalogue', 'woo-all-in-one-catalogue'),
            'manage_options',
            'wooaiocatalogue',
            'wooaioc_render_settings_page',
            10
        );
    } else {
        add_menu_page(
            __('Catalogue', 'woo-all-in-one-catalogue'),
            __('Product Catalogue', 'woo-all-in-one-catalogue'),
            'manage_options',
            'wooaiocatalogue',
            'wooaioc_render_settings_page',
            'dashicons-hammer',
            12
        );
    }
}
add_action( 'admin_menu', 'wooaioc_admin_menu', 40 );

function wooaioc_render_settings_page() {
    $allowed_tabs = wooaioc_get_allowed_tabs();
    $allowed_tabs_keys = array_keys($allowed_tabs);
    $default_tab = 'api-settings';
    $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : $default_tab;

    if (!in_array($active_tab, $allowed_tabs_keys)) {
        $active_tab = $default_tab;
    }

    include WOOAIOCATALOGUE_PATH . '/parts/admin/display.php';
}

function wooaioc_get_allowed_tabs() {
    return array(
        'api-settings' => array(
            'title' => __('API Settings', 'woo-all-in-one-catalogue')
        ),
        'catalogue-settings' => array(
            'title' => __('Catalogue Settings', 'woo-all-in-one-catalogue')
        ),
    );
}