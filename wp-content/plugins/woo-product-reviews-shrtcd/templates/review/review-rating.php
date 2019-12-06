<?php
/**
 * The template to display the reviewers star rating in reviews
 *
 * @var $show_schema
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $comment;
$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

if ( $rating && wc_review_ratings_enabled() ) {
    if ( 0 < $rating ) {
        $label = sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating );
        $itemprop_ratin_value = $show_schema ? ' itemprop="ratingValue"' : '';
        ?>
        <div class="star-rating" role="img"aria-label="<?php echo $label ?>"<?php echo $show_schema ? ' itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"' : ''; ?>>
            <span style="width:<?php echo ( ( $rating / 5 ) * 100 ); ?>%">
                <?php echo sprintf( esc_html__( 'Rated %s out of 5', 'woocommerce' ), '<strong class="rating"' . $itemprop_ratin_value . '>' . esc_html( $rating ) . '</strong>' ); ?>
            </span>
        </div>
        <?php
    }
}
?>
