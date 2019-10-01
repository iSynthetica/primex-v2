<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Magiccompass
 * @since 1.0
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$layout = 'right-sidebar';
?>

<?php get_header(); ?>

<!-- Page Title ============================================= -->
<section id="page-title">
    <div class="container clearfix">
        <h1 class="entry-title"><?php echo __('Blog', 'primex'); ?></h1>
        <span>Page Content on the Left &amp; Sidebar on the Right</span>
        <?php snth_the_breadcrumbs(); ?>
    </div>
</section>

<!-- Content ============================================= -->
<section id="content">
    <div class="content-wrap">
        <div class="container clearfix">
            <?php
            if ('right-sidebar' === $layout) {
                ?>
                <div class="postcontent nobottommargin clearfix">
                    <?php
                    if ( have_posts() ) {
                        ?>
                        <div id="posts">
                            <?php
                            while ( have_posts() ) {
                                the_post();
                                snth_show_template('blog/classic.php');
                            }

                            snth_pagination();
                            ?>
                        </div>
                        <?php
                    } else {
                        snth_show_template('blog/nav.php');
                    }
                    ?>
                </div>

                <div class="sidebar nobottommargin col_last clearfix">
                    <?php get_sidebar(); ?>
                </div>
                <?php
            } elseif ('left-sidebar' === $layout) {
                ?>
                <div class="postcontent nobottommargin col_last clearfix">
                    <?php

                    ?>
                </div>

                <div class="sidebar nobottommargin clearfix">
                    <?php get_sidebar(); ?>
                </div>
                <?php
            } else {
                ?>

                <?php
            }
            ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
