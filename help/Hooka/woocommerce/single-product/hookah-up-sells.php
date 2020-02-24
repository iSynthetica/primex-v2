<?php
/**
 * Single Product Up-Sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/up-sells.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_field = get_field('upsells_products_title', 'options');
$title = $title_field ? $title_field : __( 'With this product usually customers buy', 'snthwp' );

if ( $upsells_ids ) : ?>

	<div id="bestsellers" class="up-sells upsells products">
        <h2 class="section-title font-alt mb-70 mb-sm-40">
            <?php echo $title ?>
        </h2>

        <div class="row">
            <?php snth_woo_show_products_slider($upsells_ids); ?>
        </div>
	</div>

<?php endif;
