<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Service
 * @subpackage Woo_All_In_One_Service/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Service_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_All_In_One_Service_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_All_In_One_Service_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-service-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_All_In_One_Service_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_All_In_One_Service_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-service-public.js', array( 'jquery' ), $this->version, false );

	}

	public function register_service_post_type() {
        $labels = array(
            'name'                  => _x( 'Repairs', 'Post Type General Name', 'woo-all-in-one-service' ),
            'singular_name'         => _x( 'Repair', 'Post Type Singular Name', 'woo-all-in-one-service' ),
            'menu_name'             => __( 'Repairs', 'woo-all-in-one-service' ),
            'name_admin_bar'        => __( 'Repair', 'woo-all-in-one-service' ),
            'archives'              => __( 'Repair Archives', 'woo-all-in-one-service' ),
            'attributes'            => __( 'Repair Attributes', 'woo-all-in-one-service' ),
            'parent_item_colon'     => __( 'Parent Repair:', 'woo-all-in-one-service' ),
            'all_items'             => __( 'All Repairs', 'woo-all-in-one-service' ),
            'add_new_item'          => __( 'Add New Repair', 'woo-all-in-one-service' ),
            'add_new'               => __( 'Add New', 'woo-all-in-one-service' ),
            'new_item'              => __( 'New Repair', 'woo-all-in-one-service' ),
            'edit_item'             => __( 'Edit Repair', 'woo-all-in-one-service' ),
            'update_item'           => __( 'Update Repair', 'woo-all-in-one-service' ),
            'view_item'             => __( 'View Repair', 'woo-all-in-one-service' ),
            'view_items'            => __( 'View Repairs', 'woo-all-in-one-service' ),
            'search_items'          => __( 'Search Repair', 'woo-all-in-one-service' ),
            'not_found'             => __( 'Not found', 'woo-all-in-one-service' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'woo-all-in-one-service' ),
            'featured_image'        => __( 'Featured Image', 'woo-all-in-one-service' ),
            'set_featured_image'    => __( 'Set featured image', 'woo-all-in-one-service' ),
            'remove_featured_image' => __( 'Remove featured image', 'woo-all-in-one-service' ),
            'use_featured_image'    => __( 'Use as featured image', 'woo-all-in-one-service' ),
            'insert_into_item'      => __( 'Insert into Repair', 'woo-all-in-one-service' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Repair', 'woo-all-in-one-service' ),
            'items_list'            => __( 'Repairs list', 'woo-all-in-one-service' ),
            'items_list_navigation' => __( 'Repairs list navigation', 'woo-all-in-one-service' ),
            'filter_items_list'     => __( 'Filter Repairs list', 'woo-all-in-one-service' ),
        );
        $rewrite = array(
            'slug'                  => 'repairs',
            'with_front'            => true,
            'pages'                 => true,
            'feeds'                 => false,
        );
        $capabilities = array(
            'edit_post'             => 'edit_repair',
            'read_post'             => 'read_repair',
            'delete_post'           => 'delete_repair',
            'edit_posts'            => 'edit_repairs',
            'edit_others_posts'     => 'edit_others_repairs',
            'publish_posts'         => 'publish_repairs',
            'read_private_posts'    => 'read_private_repairs',
        );
        $args = array(
            'label'                 => __( 'Repair', 'woo-all-in-one-service' ),
            'description'           => __( 'Post Type Description', 'woo-all-in-one-service' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'hierarchical'          => true,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 12,
            'menu_icon'             => 'dashicons-hammer',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => $rewrite,
            'capabilities'          => $capabilities,
        );
        register_post_type( 'repair', $args );
    }
}
