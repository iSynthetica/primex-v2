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

if (!function_exists('wooaio_is_plugin_active_simple')) {
    function wooaio_is_plugin_active_simple( $plugin ) {
        return (
            in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
            ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
        );
    }
}

if (!function_exists('wooaio_is_plugin_active_by_file')) {
    function wooaio_is_plugin_active_by_file( $plugin_file ) {
        foreach ( wooaio_get_active_plugins() as $active_plugin ) {
            $active_plugin = explode( '/', $active_plugin );

            if ( isset( $active_plugin[1] ) && $plugin_file === $active_plugin[1] ) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('wooaio_get_active_plugins')) {
    function wooaio_get_active_plugins() {
        $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
        }

        return $active_plugins;
    }
}