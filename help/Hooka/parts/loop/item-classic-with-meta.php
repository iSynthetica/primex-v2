<?php
/**
 * Classic template Loop Item
 *
 * @package Hooka/Parts/Loop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<!-- Post -->
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="blog-item">

        <!-- Date -->
        <div class="blog-item-date">
            <span class="date-num">05</span>Feb
        </div>

        <!-- Post Title -->
        <h2 class="blog-item-title font-alt"><a href="blog-single-sidebar-right.html"><?php the_title(); ?></a></h2>

        <!-- Author, Categories, Comments -->
        <div class="blog-item-data">
            <a href="#"><i class="fa fa-user"></i> John Doe</a>
            <span class="separator">&nbsp;</span>
            <i class="fa fa-folder-open"></i>
            <a href="">Design</a>, <a href="#">Branding</a>
            <span class="separator">&nbsp;</span>
            <a href="#"><i class="fa fa-comments"></i> 5 Comments</a>
        </div>

        <!-- Media Gallery -->
        <div class="blog-media">
            <?php
            if('' !== get_the_post_thumbnail()) {
                ?>
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail( 'large' ); ?>
                </a>
                <?php
            }
            ?>
        </div>

        <!-- Text Intro -->
        <div class="blog-item-body">
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris non laoreet dui. Morbi lacus massa,
                euismod ut turpis molestie, tristique sodales est. Integer sit amet mi id sapien tempor molestie in nec
                massa.
            </p>
        </div>

        <!-- Read More Link -->
        <div class="blog-item-foot">
            <a href="blog-single-sidebar-right.html" class="btn btn-mod btn-round  btn-small">Read More <i
                        class="fa fa-angle-right"></i></a>
        </div>

    </div>
</article>
<!-- End Post -->