<?php
/**
 * Loop Product Item
 *
 * @package Hooka/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="carousel-item">
    <div class="post-prev-img">

        <a href="<?php echo get_the_permalink(); ?>">
            <?php echo woocommerce_get_product_thumbnail(); ?>
        </a>

    </div>

    <div class="post-prev-title font-alt align-center">
        <a href="<?php echo get_the_permalink(); ?>">
            <?php echo '<h3 class="woocommerce-loop-product__title">' . get_the_title() . '</h3>'; ?>
        </a>
    </div>

    <div class="post-prev-text align-center">
        <?php wc_get_template( 'loop/price.php' ); ?>
    </div>

    <div class="post-prev-more align-center">
        <?php woocommerce_template_loop_add_to_cart( array() ); ?>
    </div>
</div>