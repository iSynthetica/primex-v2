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
            <span class="date-num"><?php echo get_the_date('j') ?></span><?php echo get_the_date('M') ?>
        </div>

        <!-- Post Title -->
        <h2 class="blog-item-title font-alt"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

        <!-- Author, Categories, Comments -->
        <div class="blog-item-data">
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
            <?php the_excerpt(); ?>
        </div>

        <!-- Read More Link -->
        <div class="blog-item-foot">
            <a href="<?php the_permalink(); ?>" class="btn btn-mod btn-round  btn-small">
                <?php echo __('Read more', 'snthwp') ?> <i class="fa fa-angle-right"></i>
            </a>
        </div>

    </div>
</article>
<!-- End Post -->