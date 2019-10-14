<?php
/**
 * Sidebars
 *
 * @package Magiccompass/Includes
 */

function snth_register_sidebars() {
    $sidebars = apply_filters( 'snth_sidebars', array() );

    if (empty($sidebars)) {
        return;
    }

    foreach ($sidebars as $sidebar) {
        register_sidebar($sidebar);
    }
}

add_action( 'widgets_init', 'snth_register_sidebars' );