<?php
$settings = array(
    'title' => array(
        'title' 		=> __( 'Shipping title', 'flexible-shipping' ),
        'type' 			=> 'text',
        'description' 	=> __( 'Visible only to admin in WooCommerce settings.', 'flexible-shipping' ),
        'default'		=> __('Courier Shipping', 'woo-all-in-one-np'),
        'desc_tip'		=> true
    ),
    'courier_settings' => array(
        'title'         => __( 'Courier settings', 'woo-all-in-one-np' ),
        'type'          => 'courier_settings',
        'description'   => __( 'Create one or more locations for local pickup', 'woo-all-in-one-np' ),
    ),
);

return $settings;