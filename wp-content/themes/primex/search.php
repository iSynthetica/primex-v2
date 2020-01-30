<?php
/**
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$layout = 'right-sidebar';
?>

<?php get_header(); ?>

<!-- Page Title ============================================= -->
<section id="page-title">
    <div class="container clearfix">
        <h1 class="entry-title"><?php echo __('Search Page', 'primex'); ?></h1>
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

                            $pagination_args = array(
                                'pagination_class' => 'pagination justify-content-center pagination-lg pagination-rounded '
                            );

                            snth_pagination($pagination_args);
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
