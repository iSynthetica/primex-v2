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
    <?php echo do_shortcode('[snth_cart_icon]'); ?>

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
