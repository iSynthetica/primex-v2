<?php
function snth_settings_sidebars($sidebars) {
    return array(
        array(
            'id' => 'footer1',
            'name' => __('Footer 1', 'primex'),
            'description' => __('The first footer sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'footer2',
            'name' => __('Footer 2', 'primex'),
            'description' => __('The second footer sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'footer3',
            'name' => __('Footer 3', 'primex'),
            'description' => __('The third footer sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'footer4',
            'name' => __('Footer 4', 'primex'),
            'description' => __('The fourth footer sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'footer5',
            'name' => __('Footer 5', 'primex'),
            'description' => __('The fifth footer sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'blog-sidebar',
            'name' => __('Blog Sidebar', 'primex'),
            'description' => __('Blog sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'shop-page-sidebar',
            'name' => __('Shop Sidebar', 'primex'),
            'description' => __('Shop Sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'product-category-sidebar',
            'name' => __('Product Category Sidebar', 'primex'),
            'description' => __('Product Category Sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        ),
        array(
            'id' => 'product-page-sidebar',
            'name' => __('Product Page Sidebar', 'primex'),
            'description' => __('Product Page Sidebar.', 'primex'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>',
        )
    );
}
add_filter( 'snth_sidebars', 'snth_settings_sidebars', 999 );

function snth_settings_shortcodes($shortcodes) {
    return array(
        array(
            'id' => 'snth_widget_recent_posts',
            'callback' => 'snth_widget_recent_posts',
        ),
        array(
            'id' => 'snth_widget_blogroll',
            'callback' => 'snth_widget_blogroll',
        ),
        array(
            'id' => 'snth_cart_icon',
            'callback' => 'snth_cart_icon',
        ),
        array(
            'id' => 'snth_social',
            'callback' => 'snth_social',
        ),
        array(
            'id' => 'snth_phones_header',
            'callback' => 'snth_phones_header',
        ),
    );
}
add_filter('snth_shortcodes', 'snth_settings_shortcodes', 999);
