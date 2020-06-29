<?php
/**
 * Single product
 * content-single-product.php
 */

function snth_wc_template_after_price_line() {
    ?>
    <div class="clear"></div>
    <div class="line"></div>
    <?php
}

function snth_wc_get_gallery_image_html( $attachment_id, $index = 1, $main_image = false ) {
    $gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
    $thumbnail_size    = array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] );
    $image_size        = $main_image ? 'woocommerce_single' : $thumbnail_size;
    $full_size         = 'full';
    $thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
    $full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
    $alt_text          = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
    $image             = wp_get_attachment_image(
        $attachment_id,
        $image_size,
        false,
        array(
            'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
            'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
            'data-src'                => esc_url( $full_src[0] ),
            'data-large_image'        => esc_url( $full_src[0] ),
            'data-large_image_width'  => esc_attr( $full_src[1] ),
            'data-large_image_height' => esc_attr( $full_src[2] ),
            'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
        )
    );

    if ($main_image) {
        return '<a href="' . esc_url( $full_src[0] ) . '" data-lightbox="gallery-item">' . $image . '</a>';
    } else {
        return '<a href="#" data-index="'.$index.'">' . $image . '</a>';
    }

}

function snth_wc_product_additional_information_heading($title) {
    return false;
}

function snth_wc_product_description_heading($title) {
    return false;
}

function snth_wc_review_display_gravatar( $comment ) {
    ?>
    <div class="comment-meta">
        <div class="comment-author vcard">
            <span class="comment-avatar clearfix">
                <?php echo get_avatar( $comment, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '' ); ?>
            </span>
        </div>
    </div>
    <?php
}

function snth_wc_review_display_meta($comment) {
    ?>
    <div class="comment-author">John Doe<span><a href="#" title="Permalink to this comment">April 24, 2014 at 10:46AM</a></span></div>
    <?php
}

function woocommerce_review_display_comment_text($comment) {
    echo '<div class="description">';
    comment_text();
    echo '</div>';
}

function snth_wc_get_star_rating_html($html, $rating, $count) {
    $html = '';
    $width = ( $rating / 5 ) * 100;
    ob_start();
    ?>
    <span class="rating-star-container">
        <span class="rating-back"></span>
        <span class="rating-front" style="width: <?php echo $width ?>%"></span>
    </span>
    <?php

    $html .= ob_get_clean();

    return $html;
}

function snth_wc_template_loop_product_image_start() {
    ?>
    <div class="product-image">
    <a href="<?php echo get_permalink() ?>">
    <?php
}

/**
 * Get the product thumbnail for the loop.
 */
function snth_wc_template_loop_product_thumbnail() {
    add_filter('wp_get_attachment_image_attributes', 'snth_wc_add_lazy_load_image', 100, 3);
    echo woocommerce_get_product_thumbnail(); // WPCS: XSS ok.
    remove_filter('wp_get_attachment_image_attributes', 'snth_wc_add_lazy_load_image', 100);
}

function snth_wc_template_loop_product_image_end() {
    ?>
    </a>
    </div>
    <?php
}

function snth_wc_template_loop_product_quick_view_start() {
    ?>
    <div class="product-quick-view">
    <?php
}

function snth_wc_template_loop_quick_view() {
    global $post, $product;
    $short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );
    $attachment_ids = array();
    $attachment_ids[] = $product->get_image_id();
    $attachment_ids = array_merge($attachment_ids, $product->get_gallery_image_ids());

    if ( ! $short_description ) {
        return;
    }
    ?>
    <button class="button button-border button-mini button-border-thin btn-block button-reveal product-modal-desc-open">
        <i class="fas fa-info"></i><span><?php echo __('Quick view', 'primex') ?></span>
    </button>

    <div class="modal-content" style="display: none;">

        <div class="modal-header">
            <div class="row" style="width: 100%;">
                <div class="col-12">
                    <h4 class="modal-title" id="myModalLabel"><?php echo $post->post_title; ?></h4>
                </div>
            </div>

            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <?php
                    if (!empty($attachment_ids)) {
                        ?>
                        <div style="padding: 0 15px 25px 15px">
                            <div class="product-modal-desc-images owl-carousel">
                                <?php
                                foreach ( $attachment_ids as $attachment_id ) {
                                    $index = 0;
                                    ?>
                                    <div class="item">
                                        <?php
                                        echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', snth_wc_get_gallery_image_html( $attachment_id, $index, true ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
                                        ?>
                                    </div>
                                    <?php
                                    $index++;
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="col-12 col-md-6">
                    <div class="woocommerce-product-details__short-description">
                        <?php echo $short_description; // WPCS: XSS ok. ?>
                    </div>

                    <div class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'product-price' ) );?>">
                        <?php echo $product->get_price_html(); ?>
                    </div>
                    <?php woocommerce_template_loop_add_to_cart(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function snth_wc_template_loop_product_quick_view_end() {
    ?>
    </div>
    <?php
}

function snth_wc_template_loop_product_desc_start() {
    ?>
    <div class="product-desc center">
    <?php
}

/**
 * Show the product title in the product loop. By default this is an H2.
 */
function snth_wc_template_loop_product_title() {
    global $post;
    ?>
    <div class="product-title">
        <h3>
            <a href="<?php echo get_permalink() ?>">
                <?php echo get_the_title(); ?>
            </a>
        </h3>
    </div>
    <?php
}

function snth_wc_template_loop_price() {
    ?>
    <div class="product-price">
        <?php wc_get_template( 'loop/price.php' ); ?>
    </div>
    <?php
}

function snth_wc_template_loop_product_desc_end() {
    ?>
    </div>
    <?php
}

function snth_wc_template_loop_product_rating() {
    global $product;

    if ( ! wc_review_ratings_enabled() ) {
        return;
    }

    $rating_count = $product->get_rating_count();
    $review_count = $product->get_review_count();
    $average      = $product->get_average_rating();

    if ($rating_count > 0) {
        echo '<div class="product-loop-ratings center">';
        echo wc_get_rating_html( $average, $rating_count ); // WPCS: XSS ok.
        echo '</div>';
    }
}

/**
 * Get the add to cart template for the loop.
 *
 * @param array $args Arguments.
 */
function snth_wc_template_loop_add_to_cart( $args = array() ) {
    global $product;

    if ( $product ) {
        $defaults = array(
            'quantity'   => 1,
            'class'      => implode(
                ' ',
                array_filter(
                    array(
                        'button',
                        'product_type_' . $product->get_type(),
                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                        $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                    )
                )
            ),
            'attributes' => array(
                'data-product_id'  => $product->get_id(),
                'data-product_sku' => $product->get_sku(),
                'aria-label'       => $product->add_to_cart_description(),
                'rel'              => 'nofollow',
            ),
        );

        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

        if ( isset( $args['attributes']['aria-label'] ) ) {
            $args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
        }

        wc_get_template( 'loop/add-to-cart.php', $args );
    }
}

function snth_wc_product_loop_carousel_start( $echo = true, $items_xl = 4, $items_lg = 3, $items_md = 2 ) {
    ob_start();

    wc_set_loop_prop( 'loop', 0 );

    ?>
    <div id="oc-product" class="owl-carousel product-carousel carousel-widget" data-margin="30" data-pagi="false" data-autoplay="5000" data-items-xs="1" data-items-md="<?php echo $items_md ?>" data-items-lg="<?php echo $items_lg ?>" data-items-xl="<?php echo $items_xl ?>">
    <?php

    $loop_start = ob_get_clean();

    if ( $echo ) {
        echo $loop_start; // WPCS: XSS ok.
    } else {
        return $loop_start;
    }
}

function snth_wc_product_loop_carousel_end( $echo = true ) {
    ob_start();

    ?>
    </div>
    <?php

    $loop_end = ob_get_clean();

    if ( $echo ) {
        echo $loop_end; // WPCS: XSS ok.
    } else {
        return $loop_end;
    }
}

function snth_display_catalogue_item_description($product) {
    $short_description = $product->get_short_description();

    if ( ! $short_description ) {
        return;
    }
    ?>
    <div class="product-quick-view" style="padding-top: 0;">
        <button class="button button-border button-mini button-border-thin btn-block button-reveal product-modal-desc-open">
            <i class="fas fa-info"></i><span><?php echo __('Quick view', 'primex') ?></span>
        </button>

        <div class="modal-content" style="display: none;">
            <div class="row">
                <div class="col-12 col-md-6">
                    pictures
                </div>

                <div class="col-12 col-md-6">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel"><?php echo $product->get_name(); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="woocommerce-product-details__short-description">
                            <?php echo $short_description; // WPCS: XSS ok. ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function snth_display_catalogue_item_add_to_cart($product) {
    ?>
    <button class="button button-reveal button-mini catalogue-item-add-to-cart" type="button" data-id="<?php echo $product->get_id(); ?>"><i class="fas fa-cart-plus"></i><span><?php echo __( 'Add to cart', 'woocommerce' ); ?></span>
    </button>
    <?php
}

function snth_wc_add_lazy_load_image($attr, $attachment, $size) {
    if (empty($attr['data-lazyload']) && !empty($attr["src"])) {
        $attr['data-lazyload'] = $attr["src"];
        $attr["src"] = SNTH_IMAGES_URL . '/blank.svg';

        if (!empty($attr["srcset"])) {
            unset($attr["srcset"]);
        }
    }

    return $attr;
}

function snth_display_dont_call_me_back_checkbox() {
    ?>
    <div class="dont_call_me_back_container mb-2 alert alert-warning" style="display: block;width: 100%;line-height: 1;" role="alert">
        <input type="checkbox" name="dont_call_me_back" id="dont_call_me_back" value="yes">
        <label class="mb-0" style="color:#856404;" for="dont_call_me_back"><?php echo __("Don't call me back", 'primex') ?></label>
    </div>
    <?php
}

function snth_save_dont_call_me_back($order_id) {
    if ( ! empty( $_POST['dont_call_me_back'] ) && 'yes' === $_POST['dont_call_me_back'] ) {
        update_post_meta( $order_id, 'dont_call_me_back', sanitize_text_field( $_POST['dont_call_me_back'] ) );
    }
}

function snth_admin_order_display_dont_call_me_back($order) {
    $dont_call_me_back = get_post_meta( $order->get_id(), 'dont_call_me_back', true );

    if (!empty($dont_call_me_back) && 'yes' === $dont_call_me_back) {
        ?>
        <p>
            <strong><?php echo __("Customer asked to not call back", 'primex') ?></strong>
        </p>
        <?php
    }
}