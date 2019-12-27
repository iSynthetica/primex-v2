<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>
<div class="single-product">
    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

        <div class="col_half">
            <?php
            /**
             * Hook: woocommerce_before_single_product_summary.
             *
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
            ?>
        </div>

        <div class="col_half col_last product-desc">
            <div class="summary entry-summary">
                <?php
                /**
                 * Hook: woocommerce_single_product_summary.
                 *
                 * @unhooked woocommerce_template_single_title - 5
                 * @hooked woocommerce_template_single_rating - 10
                 * @hooked woocommerce_template_single_price - 10
                 * @hooked woocommerce_template_single_excerpt - 20
                 * @hooked woocommerce_template_single_add_to_cart - 30
                 * @hooked woocommerce_template_single_meta - 40
                 * @hooked woocommerce_template_single_sharing - 50
                 * @hooked WC_Structured_Data::generate_product_data() - 60
                 */
                do_action( 'woocommerce_single_product_summary' );
                ?>
            </div>
        </div>
    </div>
</div>

<div class="clear"></div><div class="line"></div>

<?php
/**
 * Hook: woocommerce_after_single_product_summary.
 *
 * @hooked woocommerce_output_product_data_tabs - 10
 * @hooked woocommerce_upsell_display - 15
 * @hooked woocommerce_output_related_products - 20
 */
do_action( 'woocommerce_after_single_product_summary' );
?>

<div class="col_full nobottommargin">

    <h4>Related Products</h4>

    <div id="oc-product" class="owl-carousel product-carousel carousel-widget" data-margin="30" data-pagi="false" data-autoplay="5000" data-items-xs="1" data-items-md="2" data-items-xl="4">

        <div class="oc-item">
            <div class="product iproduct clearfix">
                <div class="product-image">
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/dress/1.jpg" alt="Checked Short Dress"></a>
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/dress/1-1.jpg" alt="Checked Short Dress"></a>
                    <div class="sale-flash">50% Off*</div>
                    <div class="product-overlay">
                        <a href="#" class="add-to-cart"><i class="icon-shopping-cart"></i><span> Add to Cart</span></a>
                        <a href="include/ajax/shop-item.html" class="item-quick-view" data-lightbox="ajax"><i class="icon-zoom-in2"></i><span> Quick View</span></a>
                    </div>
                </div>
                <div class="product-desc center">
                    <div class="product-title"><h3><a href="#">Checked Short Dress</a></h3></div>
                    <div class="product-price"><del>$24.99</del> <ins>$12.49</ins></div>
                    <div class="product-rating">
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star-half-full"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="oc-item">
            <div class="product iproduct clearfix">
                <div class="product-image">
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/pants/1-1.jpg" alt="Slim Fit Chinos"></a>
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/pants/1.jpg" alt="Slim Fit Chinos"></a>
                    <div class="product-overlay">
                        <a href="#" class="add-to-cart"><i class="icon-shopping-cart"></i><span> Add to Cart</span></a>
                        <a href="include/ajax/shop-item.html" class="item-quick-view" data-lightbox="ajax"><i class="icon-zoom-in2"></i><span> Quick View</span></a>
                    </div>
                </div>
                <div class="product-desc center">
                    <div class="product-title"><h3><a href="#">Slim Fit Chinos</a></h3></div>
                    <div class="product-price">$39.99</div>
                    <div class="product-rating">
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star-half-full"></i>
                        <i class="icon-star-empty"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="oc-item">
            <div class="product iproduct clearfix">
                <div class="product-image">
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/shoes/1-1.jpg" alt="Dark Brown Boots"></a>
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/shoes/1.jpg" alt="Dark Brown Boots"></a>
                    <div class="product-overlay">
                        <a href="#" class="add-to-cart"><i class="icon-shopping-cart"></i><span> Add to Cart</span></a>
                        <a href="include/ajax/shop-item.html" class="item-quick-view" data-lightbox="ajax"><i class="icon-zoom-in2"></i><span> Quick View</span></a>
                    </div>
                </div>
                <div class="product-desc center">
                    <div class="product-title"><h3><a href="#">Dark Brown Boots</a></h3></div>
                    <div class="product-price">$49</div>
                    <div class="product-rating">
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star-empty"></i>
                        <i class="icon-star-empty"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="oc-item">
            <div class="product iproduct clearfix">
                <div class="product-image">
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/dress/2.jpg" alt="Light Blue Denim Dress"></a>
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/dress/2-2.jpg" alt="Light Blue Denim Dress"></a>
                    <div class="product-overlay">
                        <a href="#" class="add-to-cart"><i class="icon-shopping-cart"></i><span> Add to Cart</span></a>
                        <a href="include/ajax/shop-item.html" class="item-quick-view" data-lightbox="ajax"><i class="icon-zoom-in2"></i><span> Quick View</span></a>
                    </div>
                </div>
                <div class="product-desc center">
                    <div class="product-title"><h3><a href="#">Light Blue Denim Dress</a></h3></div>
                    <div class="product-price">$19.95</div>
                    <div class="product-rating">
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star-empty"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="oc-item">
            <div class="product iproduct clearfix">
                <div class="product-image">
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/sunglasses/1.jpg" alt="Unisex Sunglasses"></a>
                    <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/sunglasses/1-1.jpg" alt="Unisex Sunglasses"></a>
                    <div class="sale-flash">Sale!</div>
                    <div class="product-overlay">
                        <a href="#" class="add-to-cart"><i class="icon-shopping-cart"></i><span> Add to Cart</span></a>
                        <a href="include/ajax/shop-item.html" class="item-quick-view" data-lightbox="ajax"><i class="icon-zoom-in2"></i><span> Quick View</span></a>
                    </div>
                </div>
                <div class="product-desc center">
                    <div class="product-title"><h3><a href="#">Unisex Sunglasses</a></h3></div>
                    <div class="product-price"><del>$19.99</del> <ins>$11.99</ins></div>
                    <div class="product-rating">
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star3"></i>
                        <i class="icon-star-empty"></i>
                        <i class="icon-star-empty"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
