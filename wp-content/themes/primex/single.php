<?php
$layout = 'right-sidebar';
$layout = 'left-sidebar';
$layout = 'fullwidth';
?>

<?php get_header('dark');?>

<?php
/* Start the Loop */
while ( have_posts() ) :
    the_post();
    $categories_list = get_the_category_list( ', ' );

    ob_start();
    ?>
    <div class="single-post nobottommargin">
        <div class="entry clearfix">
            <ul class="entry-meta clearfix">
                <li><i class="far fa-calendar-alt"></i> <?php echo get_the_date(); ?></li>
                <li><i class="far fa-user"></i> <?php echo get_the_author(); ?></li>
                <?php
                if ($categories_list) {
                    ?>
                    <li><i class="far fa-folder-open"></i> <?php echo $categories_list; ?></li>
                    <?php
                }
                ?>
                <li><a href="blog-single.html#comments"><i class="far fa-comments"></i> 13 Comments</a></li>
            </ul><!-- .entry-meta end -->

            <?php
            if ( '' !== get_the_post_thumbnail() ) {
                ?>
                <div class="entry-image">
                    <a href="<?php echo esc_url( get_permalink() ); ?>">
                        <img class="image_fade" src="<?php the_post_thumbnail_url( 'full' ); ?>" alt="Standard Post with Image">
                    </a>
                </div>
                <?php
            }
            ?>

            <div class="entry-content notopmargin">
                <?php the_content(); ?>

                <?php
                snth_link_pages(
                    array(
                        'before' => '<ul class="pagination pagination-transparent pagination-rounded"><li  class="page-item disabled"><span class="page-link">' . __( 'Pages:', 'primex' ) . '</span></li>',
                        'after'  => '</ul>',
                        'link_before'  => '<li  class="page-item">',
                        'link_after'  => '</li>',
                        'link_before_disabled'  => '<li  class="page-item active">',
                        'link_after_disabled'  => '</li>',
                    )
                );?>
            </div>
        </div>

        <div class="post-navigation clearfix">
            <?php
            echo snth_get_the_post_navigation(
                array(
                    'prev_text' => '<span class="screen-reader-text">' . __( 'Previous Post', 'primex' ) . '</span> <span class="nav-title"><span class="nav-title-icon-wrapper"><i class="fas fa-angle-left"></i> ' . '</span>%title</span>',
                    'next_text' => '<span class="screen-reader-text">' . __( 'Next Post', 'primex' ) . '</span> <span class="nav-title">%title<span class="nav-title-icon-wrapper"> <i class="fas fa-angle-right"></i>' . '</span></span>',
                )
            );
            ?>
        </div>

        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) {
            comments_template();
        }
        ?>
    </div>
    <?php
    $post_content = ob_get_clean();
    ?>

    <section id="page-title">
        <div class="container clearfix">
            <h1><?php echo get_the_title(); ?></h1>
            <span>Page Content on the Left &amp; Sidebar on the Right</span>
            <?php snth_the_breadcrumbs(); ?>
        </div>
    </section><!-- #page-title end -->

    <section id="content">
        <div class="content-wrap">
            <div class="container clearfix">
                <?php
                if ('right-sidebar' === $layout) {
                    ?>
                    <div class="postcontent nobottommargin clearfix">
                        <?php echo $post_content; ?>
                    </div>

                    <div class="sidebar nobottommargin col_last clearfix">
                        <?php get_sidebar(); ?>
                    </div>
                    <?php
                } elseif ('left-sidebar' === $layout) {
                    ?>
                    <div class="postcontent nobottommargin col_last clearfix">
                        <?php echo $post_content; ?>
                    </div>

                    <div class="sidebar nobottommargin clearfix">
                        <?php get_sidebar(); ?>
                    </div>
                    <?php
                } else {
                    echo $post_content;
                }
                ?>
            </div>
        </div>
    </section>
    <?php

endwhile; // End of the loop.
?>


<?php get_footer(); ?>
