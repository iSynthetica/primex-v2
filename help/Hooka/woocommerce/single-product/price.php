<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $post;

$is_hookah = snth_is_product('hookahs');
$is_hookah_hoses = snth_is_product('hookah-hoses');
$is_hookah_hoses = snth_is_product_hookah_hose();
$wholesale_table = false;
$hookah_hoses_wholesale_table = false;

$wholesale_group = false;
$wholesale_group_array = wp_get_post_terms( $post->ID, 'wholesale_group' );

if (!empty($wholesale_group_array)) {
    foreach ($wholesale_group_array as $wholesale_group_object) {
        $wholesale_group = $wholesale_group_object->slug;

        continue;
    }
}

if ($wholesale_group) {
    $wholesale_table = joints_woo_get_product_wholesale_table($wholesale_group, $post->ID);
}

if ($is_hookah_hoses) {
    $hookah_hoses_wholesale_table = joints_woo_get_hookah_hoses_discount_table($post->ID);
}

?>
<div class="price mt-10 mb-10">
    <div class="row">
        <?php
        if ( $wholesale_table || $hookah_hoses_wholesale_table ) {
            ?>
            <div class="col-md-7">
                <small class="font-alt"><?php _e('Price', 'woocommerce') ?>:</small> <span class="lead"><?php echo snth_wc_get_price_html(); ?></span>
            </div>

            <div class="col-md-5 text-right">
                <button type="button" class="btn btn-mod btn-border btn-round toggle-control" data-toggle="wholesale_holder"><?php echo __('Wholesale', 'snthwp'); ?></button>
            </div>
            <?php
        } else {
            ?>
            <div class="col-xs-12">
                <small class="font-alt"><?php _e('Price', 'woocommerce') ?>:</small> <span class="lead"><?php echo snth_wc_get_price_html(); ?></span>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    if ( $wholesale_table || $hookah_hoses_wholesale_table ) {
        ?>
        <div class="row">
            <div class="col-xs-12">
                <div id="wholesale_holder" class="toggle-container">
                        <?php
                        if ($hookah_hoses_wholesale_table) {
                            $description = get_field('hookah_hoses_with_hookah_discount_description', 'options');
                            ?>
                            <div class="wholesale_inner pt-20 <?php echo $wholesale_table ? '' : 'pb-20' ?>">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <?php echo !empty($description) ? $description . ' ' : '' ?><strong style="display: inline-block;padding-left: 25px">

                                            <?php
                                            $currency = apply_filters( 'wcml_price_currency', NULL );

                                            if ($currency) {
                                                echo apply_filters('wcml_formatted_price', $hookah_hoses_wholesale_table);
                                            } else {
                                                ?>
                                                <span class="woocommerce-Price-amount amount font-alt">
                                                            <?php echo $hookah_hoses_wholesale_table ?> <span class="woocommerce-Price-currencySymbol"><?php echo __(' uah', 'snthwp') ?></span>
                                                        </span>
                                                <?php
                                            }
                                            ?>

                                            <?php // echo $hookah_hoses_wholesale_table . ' ' . __(' uah', 'snthwp'); ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        if ($wholesale_table) {
                            ?>
                            <div class="wholesale_inner pt-20 pb-20">
                                <?php
                                $wholesale_description = get_field('product_discount_description', 'options');
                                if (!empty($wholesale_description)) {
                                    ?><p class="mb-10"><?php echo $wholesale_description ?></p><?php
                                }
                                foreach ($wholesale_table as $discount_row) {
                                    $min = $discount_row['discount_price']['min'];
                                    $max = $discount_row['discount_price']['max'];
                                    $price = $discount_row['discount_price']['price'];
                                    ?>
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <?php
                                            if ('' !== $min && '' !== $max) {
                                                ?>
                                                <strong><?php echo $min . ' - ' . $max; ?>:</strong>
                                                <?php
                                            } else if ('' !== $min && '' === $max) {
                                                ?>
                                                <strong><?php echo __('more than', 'snthwp') .' ' . $min; ?>:</strong>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-xs-8">
                                            <span><strong>

                                                    <?php

                                                    $currency = apply_filters( 'wcml_price_currency', NULL );

                                                    if ($currency) {
                                                        echo apply_filters('wcml_formatted_price', $price);
                                                    } else {
                                                        ?>
                                                        <span class="woocommerce-Price-amount amount font-alt">
                                                            <?php echo $price ?> <span class="woocommerce-Price-currencySymbol"><?php echo __(' uah', 'snthwp') ?></span>
                                                        </span>
                                                        <?php
                                                    }
                                                    ?>
                                                </strong></span>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
