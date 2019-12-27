<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews" class="woocommerce-Reviews">
	<div id="comments">
		<?php if ( have_comments() ) : ?>
			<ol class="commentlist clearfix">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => '&larr;',
							'next_text' => '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
        <div class="modal fade" id="reviewFormModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-body">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="reviewFormModalLabel"><?php esc_html_e( 'Add a review', 'woocommerce' ); ?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div id="review_form_wrapper">
                                <div id="review_form">
                                    <?php
                                    $commenter    = wp_get_current_commenter();
                                    $comment_form = array(
                                        /* translators: %s is product title */
                                        'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
                                        /* translators: %s is product title */
                                        'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
                                        'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
                                        'title_reply_after'   => '</span>',
                                        'comment_notes_after' => '',
                                        'logged_in_as'        => '',
                                        'comment_field'       => '',
                                    );

                                    $name_email_required = (bool) get_option( 'require_name_email', 1 );
                                    $fields              = array(
                                        'author' => array(
                                            'label'    => __( 'Name', 'woocommerce' ),
                                            'type'     => 'text',
                                            'value'    => $commenter['comment_author'],
                                            'required' => $name_email_required,
                                        ),
                                        'email' => array(
                                            'label'    => __( 'Email', 'woocommerce' ),
                                            'type'     => 'email',
                                            'value'    => $commenter['comment_author_email'],
                                            'required' => $name_email_required,
                                        ),
                                    );

                                    $comment_form['fields'] = array();

                                    $i = 1;
                                    foreach ( $fields as $key => $field ) {
                                        $last_col = '';

                                        if (2 === $i) {
                                            $last_col = ' col_last';
                                        }

                                        $field_html  = '<div class="col_half '.$last_col.' comment-form-' . esc_attr( $key ) . '">';
                                        $field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

                                        if ( $field['required'] ) {
                                            $field_html .= '&nbsp;<span class="required">*</span>';
                                        }

                                        $field_html .= '</label><input class="form-control" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' . ( $field['required'] ? 'required' : '' ) . ' /></div>';

                                        $comment_form['fields'][ $key ] = $field_html;
                                        $i++;
                                    }

                                    if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
                                        $consent = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

                                        $field_cookie =  sprintf(
                                            '<div class="comment-form-cookies-consent col_full"><div class="form-check">%s %s</div></div>',
                                            sprintf(
                                                '<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" class="form-check-input" type="checkbox" value="yes"%s />',
                                                $consent
                                            ),
                                            sprintf(
                                                '<label for="wp-comment-cookies-consent" class="form-check-label">%s</label>',
                                                __( 'Save my name, email, and website in this browser for the next time I comment.' )
                                            )
                                        );

                                        // Ensure that the passed fields include cookies consent.
                                        if ( isset( $comment_form['fields'] ) && ! isset( $comment_form['fields']['cookies'] ) ) {
                                            $comment_form['fields']['cookies'] = $field_cookie;
                                        }
                                    }

                                    $account_page_url = wc_get_page_permalink( 'myaccount' );
                                    if ( $account_page_url ) {
                                        /* translators: %s opening and closing link tags respectively */
                                        $comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
                                    }

                                    if ( wc_review_ratings_enabled() ) {
                                        $comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . '</label><select name="rating" id="rating" required>
                            <option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
                            <option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
                            <option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
                            <option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
                            <option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
                            <option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
                        </select></div>';
                                    }

                                    $comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" class="form-control" rows="5" required></textarea></p>';

                                    $comment_form['submit_button'] = '<button type="submit" id="%2$s" class="button button-reveal button-small">'.'<i class="far fa-comments"></i><span>'.esc_html__( 'Submit', 'woocommerce' ) . '</span>'.'</button>';

                                    comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-modal-form-review">
            <button class="button button-reveal button-small" data-toggle="modal" data-target="#reviewFormModal">
                <i class="far fa-comments"></i><span><?php esc_html_e( 'Add a review', 'woocommerce' ); ?></span>
            </button>
        </div>
	<?php else : ?>
		<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
