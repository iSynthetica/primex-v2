<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!function_exists('wooaio_is_plugin_activated')) {
    function wooaio_is_plugin_activated( $plugin_folder, $plugin_file ) {
        if ( wooaio_is_plugin_active_simple( $plugin_folder . '/' . $plugin_file ) ) {
            return true;
        } else {
            return wooaio_is_plugin_active_by_file( $plugin_file );
        }
    }
}