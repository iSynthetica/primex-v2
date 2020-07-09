<?php
$settings = array(
    'title' => array(
        'title' 		=> __( 'Shipping title', 'flexible-shipping' ),
        'type' 			=> 'text',
        'description' 	=> __( 'Visible only to admin in WooCommerce settings.', 'flexible-shipping' ),
        'default'		=> __('Local Pickup', 'woo-all-in-one-np'),
        'desc_tip'		=> true
    ),
    'local_pickup_locations' => array(
        'title'         => __( 'Local Pickup Locations', 'woo-all-in-one-np' ),
        'type'          => 'local_pickup_locations',
        'description'   => __( 'Create one or more locations for local pickup', 'woo-all-in-one-np' ),
    ),
);

return $settings;