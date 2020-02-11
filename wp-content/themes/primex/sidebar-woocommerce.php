<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */
?>
<aside id="secondary" role="complementary" class="sidebar-widgets-wrap">
    <?php
    if ( is_singular( 'product' ) ) {
        dynamic_sidebar( 'product-page-sidebar' );
    } else {
        if (is_shop()) {
            dynamic_sidebar( 'shop-page-sidebar' );
        } else {
            dynamic_sidebar( 'product-category-sidebar' );
        }
    }
    ?>
</aside>
