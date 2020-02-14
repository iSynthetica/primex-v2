<?php
add_action( 'admin_enqueue_scripts', 'admin_enqueue_styles' );

function admin_enqueue_styles() {
    wp_enqueue_style( 'site-css-admin', SNTH_STYLES_URL.'/admin.css', array(), SNTH_VERSION . time(), 'all' );
}