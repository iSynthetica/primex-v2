<h1>Export Users</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';

global $wpdb;

$forbidden_umeta_array = array(
        'wp_yoast_notifications',
        'rich_editing',
        'admin_color',
        'use_ssl',
        'show_admin_bar_front',
        '_yoast_wpseo_profile_updated',
        'show_welcome_panel',
        'nav_menu_recently_edited',
        'wp_dashboard_quick_press_last_post_id',
        'wpseo_ignore_tour',
        'wpseo_seen_about_version',
        'wpseo_title',
        'wpseo_metadesc',
        'wpseo_metakey',
        'wpseo_excludeauthorsitemap',
        'wpseo_content_analysis_disable',
        'session_tokens',
        'dismissed_wp_pointers',
        '_woocommerce_persistent_cart',
        'wp_user-settings',
        'wp_user-settings-time',
        'manageedit-productcolumnshidden',
        'managenav-menuscolumnshidden',
        'metaboxhidden_nav-menus',
        'wpseo_keyword_analysis_disable',
        'closedpostboxes_nav-menus',
        'closedpostboxes_product',
        'metaboxhidden_product',
        'users_per_page',
        'closedpostboxes_page',
        'metaboxhidden_page',
        'closedpostboxes_promo',
        'metaboxhidden_promo',
        'meta-box-order_post',
        'screen_layout_post',
        'closedpostboxes_post',
        'metaboxhidden_post',
        'meta-box-order_product',
        'screen_layout_product',
        'closedpostboxes_dashboard',
        'metaboxhidden_dashboard',
        'edit_product_cat_per_page',
        'edit_product_per_page',
        'manageuserscolumnshidden',
        'closedpostboxes_shop_order',
        'metaboxhidden_shop_order',
        'meta-box-order_shop_coupon',
        'screen_layout_shop_coupon',
        'manageedit-pagecolumnshidden',
        'manageedit-product_catcolumnshidden',
        'wp_media_library_mode',
        'meta-box-order_dashboard',
        'closedpostboxes_product-feed',
        'metaboxhidden_product-feed',
        'edit_comments_per_page',
        'manageedit-commentscolumnshidden',
        'screen_layout_shop_order',
        'meta-box-order_shop_order',
);

$sql = "SELECT * FROM {$wpdb->users}";
$users = $wpdb->get_results( $sql, ARRAY_A );
$users_by_id = array();

if (!empty($users)) {
    foreach ($users as $ui => $user) {
        $users_by_id[$user['ID']] = $user;
    }
}

$sql = "SELECT * FROM {$wpdb->usermeta}";
$usermeta = $wpdb->get_results( $sql, ARRAY_A );

foreach ($usermeta as $usermeta_i => $usermeta_item) {
    if (!empty($users_by_id[$usermeta_item['user_id']])) {
        if (!in_array($usermeta_item['meta_key'], $forbidden_umeta_array)) {
            $prefix = $wpdb->prefix;

            if (0 === strpos($usermeta_item['meta_key'], $prefix)) {
                $prefix_length = strlen($prefix);
                $usermeta_meta_key = substr($usermeta_item['meta_key'], $prefix_length);
            } else {
                $usermeta_meta_key = $usermeta_item['meta_key'];
            }

            if ('capabilities' === $usermeta_meta_key || 'user_level' === $usermeta_meta_key) {
                $users_by_id[$usermeta_item['user_id']][$usermeta_meta_key] = $usermeta_item['meta_value'];
            } else {
                $users_by_id[$usermeta_item['user_id']]['usermeta'][$usermeta_meta_key] = $usermeta_item['meta_value'];
            }
        }

        unset ($usermeta[$usermeta_i]);
    }
}

echo "<pre>";
print_r($usermeta);
echo "</pre>";

echo "<pre>";
print_r($users_by_id);
echo "</pre>";

$users_ser = serialize($users_by_id);

?>
<textarea id="" rows="10" style="width: 100%;max-width: 1000px;"><?php echo $users_ser; ?></textarea>
<?php

