<?php
/**
 * The template for displaying the header navigation
 *
 * @package WordPress
 * @subpackage Prime-X/Partials/Header
 * @since Prime-X 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- Primary Navigation
============================================= -->
<nav id="primary-menu">

    <?php snth_main_nav(); ?>

    <!-- Top Cart
    ============================================= -->
    <div id="top-cart">
        <a href="#" id="top-cart-trigger"><i class="fas fa-shopping-cart"></i><span>5</span></a>
        <div class="top-cart-content">
            <div class="top-cart-title">
                <h4>Shopping Cart</h4>
            </div>
            <div class="top-cart-items">
                <div class="top-cart-item clearfix">
                    <div class="top-cart-item-image">
                        <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/small/1.jpg" alt="Blue Round-Neck Tshirt" /></a>
                    </div>
                    <div class="top-cart-item-desc">
                        <a href="#">Blue Round-Neck Tshirt</a>
                        <span class="top-cart-item-price">$19.99</span>
                        <span class="top-cart-item-quantity">x 2</span>
                    </div>
                </div>
                <div class="top-cart-item clearfix">
                    <div class="top-cart-item-image">
                        <a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/shop/small/6.jpg" alt="Light Blue Denim Dress" /></a>
                    </div>
                    <div class="top-cart-item-desc">
                        <a href="#">Light Blue Denim Dress</a>
                        <span class="top-cart-item-price">$24.99</span>
                        <span class="top-cart-item-quantity">x 3</span>
                    </div>
                </div>
            </div>
            <div class="top-cart-action clearfix">
                <span class="fleft top-checkout-price">$114.95</span>
                <button class="button button-3d button-small nomargin fright">View Cart</button>
            </div>
        </div>
    </div><!-- #top-cart end -->

    <!-- Top Search
    ============================================= -->
    <div id="top-search">
        <a href="#" id="top-search-trigger"><i class="fas fa-search"></i><i class="fas fa-times"></i></a>
<!--        <form action="search.html" method="get">-->
<!--            <input type="text" name="q" class="form-control" value="" placeholder="Type &amp; Hit Enter..">-->
<!--        </form>-->
        <?php get_search_form(); ?>
    </div><!-- #top-search end -->

</nav><!-- #primary-menu end -->
