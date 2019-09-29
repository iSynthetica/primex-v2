<?php
/**
 *
 */

$categories_list = get_the_category_list( ', ' );
?>
<div class="entry clearfix">
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

    <div class="entry-title">
        <h2>
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a>
        </h2>
    </div>

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
    </ul>

    <div class="entry-content">
        <?php the_excerpt(); ?>
        <a href="blog-single.html"class="more-link">Read More</a>
    </div>
</div>
