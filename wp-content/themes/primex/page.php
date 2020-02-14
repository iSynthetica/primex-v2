<?php
$layout = 'right-sidebar';
$subtitle = !empty($subtitle) ? $subtitle : '';
?>

<?php get_header();?>

<?php
/* Start the Loop */
while ( have_posts() ) :
    the_post();

    if (get_post_thumbnail_id()) {
        $bg_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
        // var_dump(wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'));
        ?>
        <!-- Page Title
                ============================================= -->
        <section id="page-title" class="page-title-parallax page-title-dark" style="padding: 200px 0; background-image: url('<?php echo $bg_image[0] ?>'); background-size: cover; background-position: center center;" data-bottom-top="background-position:0px 400px;" data-top-bottom="background-position:0px -500px;">

            <div class="container clearfix">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                <?php
                if (!empty($subtitle)) {
                    ?><span>Page Content on the Left &amp; Sidebar on the Right</span><?php
                }
                ?>
                <?php // snth_the_breadcrumbs(); ?>
            </div>

        </section><!-- #page-title end -->
        <?php
    } else {
        ?>
        <!-- Page Title ============================================= -->
        <section id="page-title">
            <div class="container clearfix">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                <?php
                if (!empty($subtitle)) {
                    ?><span>Page Content on the Left &amp; Sidebar on the Right</span><?php
                }
                ?>
                <?php // snth_the_breadcrumbs(); ?>
            </div>
        </section><!-- #page-title end -->
        <?php
    }
    ?>

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
