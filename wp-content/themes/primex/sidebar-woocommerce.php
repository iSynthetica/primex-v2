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
        ?>
        Single product sidebar
        <?php
    } else {
        if (is_shop()) {
            ?>
            Shop sidebar
            <?php
        } else {
            ?>
            Archive shop sidebar
            <?php
        }
    }
    ?>
</aside>
