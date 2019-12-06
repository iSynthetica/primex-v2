<?php
/**
 * Review Comments Template
 *
 * @var $show_schema
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ('review' !== get_comment_type( $comment->comment_ID ) || !empty( $comment->comment_parent ) ) {
    $show_schema = false;
}
?>

<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>"<?php echo $show_schema ? ' itemprop="review" itemscope itemtype="http://schema.org/Review"' : ''; ?>>

    <div id="comment-<?php comment_ID(); ?>" class="comment_container">

        <?php echo get_avatar( $comment, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '' ); ?>

        <div class="comment-text">

            <?php wprshrtcd_get_template( 'review/review-rating.php', array('show_schema' => $show_schema) ); ?>

            <?php wprshrtcd_get_template( 'review/review-meta.php', array('show_schema' => $show_schema) ); ?>

            <div class="description"<?php echo $show_schema ? ' itemprop="reviewBody"' : ''; ?>>
                <?php echo $comment->comment_content; ?>
            </div>
        </div>
    </div>
