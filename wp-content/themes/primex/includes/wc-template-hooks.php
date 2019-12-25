<?php
/**
 * Single product
 * content-single-product.php
 */

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

add_action( 'woocommerce_single_product_summary', 'snth_wc_template_after_price_line', 12 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 30 );

add_action( 'woocommerce_after_add_to_cart_form', 'snth_wc_template_after_price_line', 10 );

/**
 * Loop Product
 */
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );


add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_image_start', 5 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_thumbnail', 8 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_image_end', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_quick_view_start', 15 );

//add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_add_to_cart', 20 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_quick_view', 25 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_quick_view_end', 35 );

add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_desc_start', 45 );

add_action( 'woocommerce_shop_loop_item_title', 'snth_wc_template_loop_product_title', 10 );

add_action( 'woocommerce_after_shop_loop_item_title', 'snth_wc_template_loop_price', 10 );
add_action( 'woocommerce_after_shop_loop_item_title', 'snth_wc_template_loop_product_desc_end', 15 );
add_action( 'woocommerce_after_shop_loop_item', 'snth_wc_template_loop_add_to_cart', 10 );