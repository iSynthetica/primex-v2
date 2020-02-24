<?php
/**
 * WP Core functions
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove Admin bar
 */
add_filter('show_admin_bar', '__return_false');

/**
 * Adding Translation Option
 */
function snth_load_translations(){
    load_theme_textdomain( 'snthwp', get_template_directory() .'/languages' );
}
add_action('after_setup_theme', 'snth_load_translations');

$current_user = wp_get_current_user();
$current_user_email = $current_user->user_email;
$super_admin_users = array(
    'syntheticafreon@gmail.com',
    'alsencha@gmail.com'
);

if( !in_array( $current_user_email, $super_admin_users ) ) {
    add_action('admin_menu', 'snth_hide_admin_menues', 999);
    add_filter('acf/settings/show_admin', 'snth_hide_admin_menu_acf');
}

/**
 * Hide admin menues items
 */
function snth_hide_admin_menues()
{
    remove_menu_page('tools.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('link-manager.php');
    remove_menu_page('plugins.php');
    remove_menu_page('options-general.php');
    remove_menu_page('themes.php');
    remove_menu_page('users.php');
    remove_menu_page('wpcf7');
    remove_menu_page('loco');
    remove_menu_page('wpseo_dashboard');
    remove_menu_page('sitepress-multilingual-cms/menu/languages.php');
    //remove_menu_page('cptui_main_menu');
    //remove_menu_page('sb-instagram-feed');
    //remove_menu_page('jetpack' );
    remove_menu_page('woocommerce' );
}

/**
 * Hide admin ACF menu item
 *
 * @param $show
 *
 * @return bool
 */
function snth_hide_admin_menu_acf( $show )
{
    return false;
}

/**
 * Disable WP updates
 *
 * @return object
 */
function snth_remove_core_updates()
{
    global $wp_version;

    return (object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
}

add_filter('pre_site_transient_update_core','snth_remove_core_updates');
add_filter('pre_site_transient_update_plugins','snth_remove_core_updates');
add_filter('pre_site_transient_update_themes','snth_remove_core_updates');

/**
 * Remove useless adminbar items
 *
 * @param $wp_admin_bar
 */
function snth_remove_toolbar_node( $wp_admin_bar )
{

    // replace 'updraft_admin_node' with your node id
    $wp_admin_bar->remove_node('wpseo-menu');
    $wp_admin_bar->remove_node('customize');
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('updates');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node('archive');

}

add_action('admin_bar_menu', 'snth_remove_toolbar_node', 999);