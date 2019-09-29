<?php
$layout = 'right-sidebar';
?>

<?php get_header('dark');?>

<?php
/* Start the Loop */
while ( have_posts() ) :
    the_post();

    ?>
    <!-- Page Title ============================================= -->
    <section id="page-title">

        <div class="container clearfix">
            <h1><?php echo get_the_title(); ?></h1>
            <span>Page Content on the Left &amp; Sidebar on the Right</span>
            <?php snth_the_breadcrumbs(); ?>
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

                        // If comments are open or we have at least one comment, load up the comment template.
                        if ( comments_open() || get_comments_number() ) {
                            comments_template();
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

                    <?php
                }
                ?>
            </div>
        </div>
    </section>
    <?php

endwhile; // End of the loop.
?>


<?php get_footer(); ?>
