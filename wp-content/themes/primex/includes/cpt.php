<?php
// Register Custom Post Type
function snth_section_template() {

    $labels = array(
        'name'                  => _x( 'Section Templates', 'Post Type General Name', 'primex' ),
        'singular_name'         => _x( 'Section Template', 'Post Type Singular Name', 'primex' ),
        'menu_name'             => __( 'Section Template', 'primex' ),
        'name_admin_bar'        => __( 'Section Template', 'primex' ),
        'archives'              => __( 'Item Archives', 'primex' ),
        'attributes'            => __( 'Item Attributes', 'primex' ),
        'parent_item_colon'     => __( 'Parent Section Template:', 'primex' ),
        'all_items'             => __( 'All Items', 'primex' ),
        'add_new_item'          => __( 'Add New Item', 'primex' ),
        'add_new'               => __( 'Add New', 'primex' ),
        'new_item'              => __( 'New Item', 'primex' ),
        'edit_item'             => __( 'Edit Item', 'primex' ),
        'update_item'           => __( 'Update Item', 'primex' ),
        'view_item'             => __( 'View Item', 'primex' ),
        'view_items'            => __( 'View Items', 'primex' ),
        'search_items'          => __( 'Search Item', 'primex' ),
        'not_found'             => __( 'Not found', 'primex' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'primex' ),
        'featured_image'        => __( 'Featured Image', 'primex' ),
        'set_featured_image'    => __( 'Set featured image', 'primex' ),
        'remove_featured_image' => __( 'Remove featured image', 'primex' ),
        'use_featured_image'    => __( 'Use as featured image', 'primex' ),
        'insert_into_item'      => __( 'Insert into item', 'primex' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'primex' ),
        'items_list'            => __( 'Section Template list', 'primex' ),
        'items_list_navigation' => __( 'Items list navigation', 'primex' ),
        'filter_items_list'     => __( 'Filter items list', 'primex' ),
    );
    $args = array(
        'label'                 => __( 'Section Template', 'primex' ),
        'description'           => __( 'Section Template for landing pages', 'primex' ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-schedule',
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'page',
    );
    register_post_type( 'section_template', $args );

}
add_action( 'init', 'snth_section_template', 0 );