<?php
/**
 * Template Name: Fullwidth Page
 */

$layout = 'fullwidth';
?>

<?php get_header();?>

<?php
/* Start the Loop */
while ( have_posts() ) :
    the_post();

    ?>
    <!-- Page Title ============================================= -->
    <section id="page-title">
        <div class="container clearfix">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            <?php
            if (!empty($subtitle)) {
                ?>
                <span>Page Content on the Left &amp; Sidebar on the Right</span>
                <?php
            }
            ?>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Pages</a></li>
                <li class="breadcrumb-item active" aria-current="page">Right Sidebar</li>
            </ol>
        </div>
    </section><!-- #page-title end -->

    <!-- Content ============================================= -->
    <section id="content">
        <div class="content-wrap">
            <div class="container clearfix">
                <?php
                if ('right-sidebar' === $layout) {
                    ?>
                    <div class="postcontent nobottommargin clearfix">
                        <?php
                        the_content();

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links">' . __( 'Pages:', 'twentynineteen' ),
                                'after'  => '</div>',
                            )
                        );
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
                        the_content();

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links">' . __( 'Pages:', 'twentynineteen' ),
                                'after'  => '</div>',
                            )
                        );
                        ?>
                    </div>

                    <div class="sidebar nobottommargin clearfix">
                        <?php get_sidebar(); ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="col_full">
                        <?php
                        the_content();

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links">' . __( 'Pages:', 'twentynineteen' ),
                                'after'  => '</div>',
                            )
                        );
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>
    <?php

    // If comments are open or we have at least one comment, load up the comment template.
    if ( comments_open() || get_comments_number() ) {
        comments_template();
    }

endwhile; // End of the loop.
?>

<?php get_footer(); ?>
