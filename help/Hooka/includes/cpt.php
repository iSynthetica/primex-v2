<?php
/**
 * Custom Post Types
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Register Custom Post Type
function snth_locations_cpt() {

    $labels = array(
        'name'                  => _x( 'Locations', 'Post Type General Name', 'snthwp' ),
        'singular_name'         => _x( 'Location', 'Post Type Singular Name', 'snthwp' ),
        'menu_name'             => __( 'Locations', 'snthwp' ),
        'name_admin_bar'        => __( 'Location', 'snthwp' ),
        'archives'              => __( 'Location Archives', 'snthwp' ),
        'attributes'            => __( 'Location Attributes', 'snthwp' ),
        'parent_item_colon'     => __( 'Parent Location:', 'snthwp' ),
        'all_items'             => __( 'All Locations', 'snthwp' ),
        'add_new_item'          => __( 'Add New Location', 'snthwp' ),
        'add_new'               => __( 'Add New', 'snthwp' ),
        'new_item'              => __( 'New Location', 'snthwp' ),
        'edit_item'             => __( 'Edit Location', 'snthwp' ),
        'update_item'           => __( 'Update Location', 'snthwp' ),
        'view_item'             => __( 'View Location', 'snthwp' ),
        'view_items'            => __( 'View Locations', 'snthwp' ),
        'search_items'          => __( 'Search Location', 'snthwp' ),
        'not_found'             => __( 'Not found', 'snthwp' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'snthwp' ),
        'featured_image'        => __( 'Featured Image', 'snthwp' ),
        'set_featured_image'    => __( 'Set featured image', 'snthwp' ),
        'remove_featured_image' => __( 'Remove featured image', 'snthwp' ),
        'use_featured_image'    => __( 'Use as featured image', 'snthwp' ),
        'insert_into_item'      => __( 'Insert into Location', 'snthwp' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Location', 'snthwp' ),
        'items_list'            => __( 'Locations list', 'snthwp' ),
        'items_list_navigation' => __( 'Locations list navigation', 'snthwp' ),
        'filter_items_list'     => __( 'Filter Locations list', 'snthwp' ),
    );
    $args = array(
        'label'                 => __( 'Location', 'snthwp' ),
        'description'           => __( 'Locations for hookah partners', 'snthwp' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-location-alt',
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
    );
    register_post_type( 'location', $args );

}
add_action( 'init', 'snth_locations_cpt', 0 );

// Register Custom Taxonomy
function snth_locations_type() {

    $labels = array(
        'name'                       => _x( 'Location Types', 'Taxonomy General Name', 'snthwp' ),
        'singular_name'              => _x( 'Location Type', 'Taxonomy Singular Name', 'snthwp' ),
        'menu_name'                  => __( 'Types', 'snthwp' ),
        'all_items'                  => __( 'All Types', 'snthwp' ),
        'parent_item'                => __( 'Parent Type', 'snthwp' ),
        'parent_item_colon'          => __( 'Parent Type:', 'snthwp' ),
        'new_item_name'              => __( 'New Type Name', 'snthwp' ),
        'add_new_item'               => __( 'Add New Type', 'snthwp' ),
        'edit_item'                  => __( 'Edit Type', 'snthwp' ),
        'update_item'                => __( 'Update Type', 'snthwp' ),
        'view_item'                  => __( 'View Type', 'snthwp' ),
        'separate_items_with_commas' => __( 'Separate Types with commas', 'snthwp' ),
        'add_or_remove_items'        => __( 'Add or remove Types', 'snthwp' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'snthwp' ),
        'popular_items'              => __( 'Popular Types', 'snthwp' ),
        'search_items'               => __( 'Search Types', 'snthwp' ),
        'not_found'                  => __( 'Not Found', 'snthwp' ),
        'no_terms'                   => __( 'No Types', 'snthwp' ),
        'items_list'                 => __( 'Types list', 'snthwp' ),
        'items_list_navigation'      => __( 'Types list navigation', 'snthwp' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => false,
    );
    register_taxonomy( 'location_type', array( 'location' ), $args );

}
add_action( 'init', 'snth_locations_type', 0 );

if ( ! function_exists( 'snth_locations_city' ) ) {

// Register Custom Taxonomy
function snth_locations_column() {

        $labels = array(
            'name'                       => _x( 'Columns', 'Taxonomy General Name', 'snthwp' ),
            'singular_name'              => _x( 'Column', 'Taxonomy Singular Name', 'snthwp' ),
            'menu_name'                  => __( 'Column', 'snthwp' ),
            'all_items'                  => __( 'All Columns', 'snthwp' ),
            'parent_item'                => __( 'Parent Column', 'snthwp' ),
            'parent_item_colon'          => __( 'Parent Column:', 'snthwp' ),
            'new_item_name'              => __( 'New Column Name', 'snthwp' ),
            'add_new_item'               => __( 'Add New Column', 'snthwp' ),
            'edit_item'                  => __( 'Edit Column', 'snthwp' ),
            'update_item'                => __( 'Update Column', 'snthwp' ),
            'view_item'                  => __( 'View Column', 'snthwp' ),
            'separate_items_with_commas' => __( 'Separate Columns with commas', 'snthwp' ),
            'add_or_remove_items'        => __( 'Add or remove Columns', 'snthwp' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'snthwp' ),
            'popular_items'              => __( 'Popular Columns', 'snthwp' ),
            'search_items'               => __( 'Search Column', 'snthwp' ),
            'not_found'                  => __( 'Not Found', 'snthwp' ),
            'no_terms'                   => __( 'No Columns', 'snthwp' ),
            'items_list'                 => __( 'Columns list', 'snthwp' ),
            'items_list_navigation'      => __( 'Columns list navigation', 'snthwp' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
        );
        register_taxonomy( 'partner_column', array( 'location' ), $args );

    }
    add_action( 'init', 'snth_locations_column', 0 );
}

// Register Custom Taxonomy
function snth_woo_wholesale_group() {

    $labels = array(
        'name'                       => _x( 'Wholesale Groups', 'Taxonomy General Name', 'snthwp' ),
        'singular_name'              => _x( 'Wholesale Group', 'Taxonomy Singular Name', 'snthwp' ),
        'menu_name'                  => __( 'Wholesale Group', 'snthwp' ),
        'all_items'                  => __( 'All Groups', 'snthwp' ),
        'parent_item'                => __( 'Parent Wholesale Group', 'snthwp' ),
        'parent_item_colon'          => __( 'Parent Wholesale Group:', 'snthwp' ),
        'new_item_name'              => __( 'New Wholesale Group Name', 'snthwp' ),
        'add_new_item'               => __( 'Add New Wholesale Group', 'snthwp' ),
        'edit_item'                  => __( 'Edit Wholesale Group', 'snthwp' ),
        'update_item'                => __( 'Update Wholesale Group', 'snthwp' ),
        'view_item'                  => __( 'View Wholesale Group', 'snthwp' ),
        'separate_items_with_commas' => __( 'Separate Wholesale Groups with commas', 'snthwp' ),
        'add_or_remove_items'        => __( 'Add or remove Wholesale Groups', 'snthwp' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'snthwp' ),
        'popular_items'              => __( 'Popular Wholesale Groups', 'snthwp' ),
        'search_items'               => __( 'Search Wholesale Groups', 'snthwp' ),
        'not_found'                  => __( 'Not Found', 'snthwp' ),
        'no_terms'                   => __( 'No Wholesale Groups', 'snthwp' ),
        'items_list'                 => __( 'Wholesale Groups list', 'snthwp' ),
        'items_list_navigation'      => __( 'Wholesale Groups list navigation', 'snthwp' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => false,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'wholesale_group', array( 'product' ), $args );

}
add_action( 'init', 'snth_woo_wholesale_group', 0 );