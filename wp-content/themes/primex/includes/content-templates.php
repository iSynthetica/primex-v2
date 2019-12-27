<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 29.03.19
 * Time: 13:55
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function snth_get_post_thumbnail_url($size = 'full') {
    global $post;

    return '';
}

function snth_the_social_share() {
    snth_show_template('global/share.php');
}

/**
 * Custom Breadcrumbs
 */
function snth_the_breadcrumbs() {
    /* == Options - Start == */
    $text['home'] = __('Home', 'snthwp');
    $text['blog'] = __('Blog', 'snthwp');
    $text['category'] = '%s';
    $text['tag'] = __('Posts tagged with "%s"', 'snthwp');
    $text['page'] = __('Page %s', 'snthwp');

    $show_home_link = 1;
    $show_blog_link = 1;
    $show_on_home = 0;
    $home_page_for_posts = 1;
    $show_current = 1;

    $is_woo_active = snth_is_woocommerce_active();
    $is_yoast_seo_active = snth_is_yoast_seo_active();

    $wrap_before = '<ol class="breadcrumb">';
    $wrap_after = '</ol>';

    $sep_item = '<li class="separator">/</li>'; // разделитель между "крошками"
    $sep_item = ''; // разделитель между "крошками"
    $sep_before = '&nbsp'; // тег перед разделителем
    $sep_after = '&nbsp'; // тег после разделителя
    $sep_before = ''; // тег перед разделителем
    $sep_after = ''; // тег после разделителя
    $sep = $sep_before . $sep_item . $sep_after;

    $link_before = '<li class="breadcrumb-item">';
    $link_after = '</li>';
    $current_link_before = '<li class="breadcrumb-item active" aria-current="page">';
    $current_link_after = '</li>';
    $link_attr = ' itemprop="item"';
    $link_in_before = '';
    $link_in_after = '';
    $link = $link_before . '<a href="%1$s"' . $link_attr . '>' . $link_in_before . '%2$s' . $link_in_after . '</a>' . $link_after;

    $current_before = '<span class="current">'; // тег перед текущей "крошкой"
    $current_after = '</span>'; // тег после текущей "крошки"
    $current_before = ''; // тег перед текущей "крошкой"
    $current_after = ''; // тег после текущей "крошки"
    /* == Options - End == */

    global $post;

    $home_url = home_url('/');
    $home_link = $link_before . '<a href="' . $home_url . '"' . $link_attr . '>' . $link_in_before . $text['home'] . $link_in_after . '</a>' . $link_after;
    $frontpage_id = get_option('page_on_front');
    $homepage_id = get_option('page_for_posts');

    if ((is_home() && !$home_page_for_posts) || is_front_page()) {
        if ($show_on_home) {
            echo $wrap_before . $home_link . $wrap_after;
        }
    }
    else {
        echo $wrap_before;

        if ($show_home_link) {
            echo $home_link;
        }

        if (is_category()) {

            if ($show_home_link) {
                echo $sep;
            }

            if ($show_blog_link) {
                echo sprintf($link, get_permalink($homepage_id), $text['blog']);
                echo $sep;
            }

            $cat = get_category(get_query_var('cat'), false);

            if ($cat->parent != 0) {
                $cats = get_category_parents($cat->parent, TRUE, $sep);
                $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);

                echo $cats;
                echo $sep;
            }

            if ( get_query_var('paged') ) {
                $cat = $cat->cat_ID;
                echo sprintf($link, get_category_link($cat), get_cat_name($cat)) . $sep . $current_before . sprintf($text['page'], get_query_var('paged')) . $current_after;
            } else {
                if ($show_current) echo $current_before . sprintf($text['category'], single_cat_title('', false)) . $current_after;
            }

        }
        elseif (is_tag()) {

            if ($show_home_link) {
                echo $sep;
            }

            if ($show_blog_link) {
                echo sprintf($link, get_permalink($homepage_id), $text['blog']);
                echo $sep;
            }

            if ( get_query_var('paged') ) {
                $tag_id = get_queried_object_id();
                $tag = get_tag($tag_id);
                echo sprintf($link, get_tag_link($tag_id), $tag->name) . $sep . $current_before . sprintf($text['page'], get_query_var('paged')) . $current_after;
            } else {
                if ($show_current) echo $current_before . sprintf($text['tag'], single_tag_title('', false)) . $current_after;
            }
        }
        elseif (is_single()) {
            $id = $post->ID;

            if ( get_post_type() === 'destination' ) {

                if ($show_home_link) {
                    echo $sep;
                }

                $id = $post->ID;
                $parent_id = ($post) ? $post->post_parent : '';

                if ($parent_id) {
                    if ($parent_id != $frontpage_id) {
                        $breadcrumbs = array();

                        while ($parent_id) {
                            $page = get_post($parent_id);

                            if ($parent_id != $frontpage_id) {
                                $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                            }

                            $parent_id = $page->post_parent;
                        }

                        $breadcrumbs = array_reverse($breadcrumbs);

                        for ($i = 0; $i < count($breadcrumbs); $i++) {
                            echo $breadcrumbs[$i];
                            if ($i != count($breadcrumbs)-1) echo $sep;
                        }

                        if ($show_current) {
                            echo $sep;
                        }
                    }
                }

                if ($show_current) {
                    echo $current_link_before . $current_before . get_the_title($id) . $current_after . $current_link_after;
                }

            } elseif ( get_post_type() === 'product' ) {

            }
            else {
                $cat = false;

                if ($is_yoast_seo_active) {
                    $cat = get_post_meta($post->ID , '_yoast_wpseo_primary_category', true);
                }

                if (!$cat) {
                    $cat = get_the_category();
                    $cat = $cat[0];
                }

                $cats = get_category_parents($cat, TRUE, $sep);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);

                if (!$show_current || get_query_var('cpage')) {
                    $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                }

                if ($show_home_link) {
                    echo $sep;
                }

                echo sprintf($link, get_permalink($homepage_id), $text['blog']);

                echo $sep . $cats;

                if ($show_current) {
                    echo $current_link_before . $current_before . get_the_title($id) . $current_after . $current_link_after;
                }
            }
        }
        elseif ( is_page() ) {
            $id = $post->ID;
            $parent_id = ($post) ? $post->post_parent : '';

            if ($show_home_link) {
                echo $sep;
            }

            if ($parent_id) {
                if ($parent_id != $frontpage_id) {
                    $breadcrumbs = array();

                    while ($parent_id) {
                        $page = get_post($parent_id);

                        if ($parent_id != $frontpage_id) {
                            $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                        }

                        $parent_id = $page->post_parent;
                    }

                    $breadcrumbs = array_reverse($breadcrumbs);

                    for ($i = 0; $i < count($breadcrumbs); $i++) {
                        echo $breadcrumbs[$i];
                        if ($i != count($breadcrumbs)-1) echo $sep;
                    }

                    if ($show_current) {
                        echo $sep;
                    }
                }
            }

            if ($show_current) {
                echo $link_before . $current_before . get_the_title($id) . $current_after . $link_after;
            }
        }
        elseif (is_home() && $home_page_for_posts) {

            if ($show_home_link) {
                echo $sep;
            }

            if ($show_current) {
                echo $link_before . $current_before . $text['blog'] . $current_after . $link_after;
            }
        }

        echo $wrap_after;
    }
}

function snth_pagination($args = array()) {
    global $wp_query;
    $big = 999999999;

    $defaults = array(
        'pagination_class' => 'pagination justify-content-center'
    );

    $args = wp_parse_args( $args, $defaults );

    $paginate_links = paginate_links( array(
        'base' => str_replace( $big, '%#%', html_entity_decode( get_pagenum_link( $big ) ) ),
        'current' => max( 1, get_query_var( 'paged' ) ),
        'total' => $wp_query->max_num_pages,
        'end_size' => 1,
        'mid_size' => 2,
        'prev_next' => true,
        'prev_text' => __( '<i class="fas fa-angle-left"></i>', 'snthwp' ),
        'next_text' => __( '<i class="fas fa-angle-right"></i>', 'snthwp' ),
        'type' => 'array',
    ) );

    if (!empty($paginate_links)) {
        foreach ($paginate_links as $key => $link) {
            $new_link = str_replace('<a class="prev page-numbers"', '<li class="page-item"><a class="page-link"', $link);
            $new_link = str_replace('<a class="next page-numbers"', '<li class="page-item"><a class="page-link"', $new_link);
            $new_link = str_replace('<a class="page-numbers"', '<li class="page-item"><a class="page-link"', $new_link);
            $new_link = str_replace('<a class=\'page-numbers\'', '<li class="page-item"><a class="page-link"', $new_link);
            $new_link = str_replace('<span class="page-numbers dots"', '<li class="page-item disabled"><span class="page-link"', $new_link);
            $new_link = str_replace('<span aria-current=\'page\' class=\'page-numbers current\'', '<li class="page-item active"><span class="page-link"', $new_link);
            $new_link = str_replace('</a>', '</a></li>', $new_link);
            $new_link = str_replace('</span>', '</span></li>', $new_link);

            $paginate_links[$key] = $new_link;
        }
    }

    if ( $paginate_links ) {
        echo '<nav aria-label="Page navigation" class="page-navigation">';
        echo '<ul class="'.$args['pagination_class'].'">';
        foreach ($paginate_links as $link) {
            echo $link;
        }
        echo '</ul>';
        echo '</nav><!--// end .pagination -->';
    }
}

function snth_link_pages( $args = '' ) {
    global $page, $numpages, $multipage, $more;

    $defaults = array(
        'before'           => '<p class="post-nav-links">' . __( 'Pages:' ),
        'after'            => '</p>',
        'link_before'      => '',
        'link_after'       => '',
        'link_before_disabled'      => '',
        'link_after_disabled'       => '',
        'aria_current'     => 'page',
        'next_or_number'   => 'number',
        'separator'        => ' ',
        'nextpagelink'     => __( 'Next page' ),
        'previouspagelink' => __( 'Previous page' ),
        'pagelink'         => '%',
        'echo'             => 1,
    );

    $params = wp_parse_args( $args, $defaults );

    /**
     * Filters the arguments used in retrieving page links for paginated posts.
     *
     * @since 3.0.0
     *
     * @param array $params An array of arguments for page links for paginated posts.
     */
    $r = apply_filters( 'wp_link_pages_args', $params );

    $output = '';
    if ( $multipage ) {
        if ( 'number' == $r['next_or_number'] ) {
            $output .= $r['before'];
            for ( $i = 1; $i <= $numpages; $i++ ) {
                $link = str_replace( '%', $i, $r['pagelink'] );

                if ( $i != $page || ! $more && 1 == $page ) {
                    $link = $r['link_before'] . _snth_link_page( $i ) . $link . '</a>' . $r['link_after'];
                } elseif ( $i === $page ) {
                    $link = $r['link_before_disabled'] . '<span class="post-page-numbers page-link current" aria-current="' . esc_attr( $r['aria_current'] ) . '">' . $link . '</span>' . $r['link_after_disabled'];
                }
                /**
                 * Filters the HTML output of individual page number links.
                 *
                 * @since 3.6.0
                 *
                 * @param string $link The page number HTML output.
                 * @param int    $i    Page number for paginated posts' page links.
                 */
                $link = apply_filters( 'wp_link_pages_link', $link, $i );

                // Use the custom links separator beginning with the second link.
                $output .= ( 1 === $i ) ? ' ' : $r['separator'];
                $output .= $link;
            }
            $output .= $r['after'];
        } elseif ( $more ) {
            $output .= $r['before'];
            $prev    = $page - 1;
            if ( $prev > 0 ) {
                $link = $r['link_before'] . _snth_link_page( $prev ) . $r['previouspagelink'] . '</a>' . $r['link_after'];

                /** This filter is documented in wp-includes/post-template.php */
                $output .= apply_filters( 'wp_link_pages_link', $link, $prev );
            }
            $next = $page + 1;
            if ( $next <= $numpages ) {
                if ( $prev ) {
                    $output .= $r['separator'];
                }
                $link = $r['link_before'] . _snth_link_page( $next ) . $r['nextpagelink'] . '</a>' . $r['link_after'];

                /** This filter is documented in wp-includes/post-template.php */
                $output .= apply_filters( 'wp_link_pages_link', $link, $next );
            }
            $output .= $r['after'];
        }
    }

    /**
     * Filters the HTML output of page links for paginated posts.
     *
     * @since 3.6.0
     *
     * @param string $output HTML output of paginated posts' page links.
     * @param array  $args   An array of arguments.
     */
    $html = apply_filters( 'wp_link_pages', $output, $args );

    if ( $r['echo'] ) {
        echo $html;
    }
    return $html;
}

function _snth_link_page( $i ) {
    global $wp_rewrite;
    $post       = get_post();
    $query_args = array();

    if ( 1 == $i ) {
        $url = get_permalink();
    } else {
        if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
            $url = add_query_arg( 'page', $i, get_permalink() );
        } elseif ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $post->ID ) {
            $url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
        } else {
            $url = trailingslashit( get_permalink() ) . user_trailingslashit( $i, 'single_paged' );
        }
    }

    if ( is_preview() ) {

        if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
            $query_args['preview_id']    = wp_unslash( $_GET['preview_id'] );
            $query_args['preview_nonce'] = wp_unslash( $_GET['preview_nonce'] );
        }

        $url = get_preview_post_link( $post, $query_args, $url );
    }

    return '<a href="' . esc_url( $url ) . '" class="post-page-numbers page-link">';
}

function snth_get_the_post_navigation( $args = array() ) {
    $args = wp_parse_args(
        $args,
        array(
            'prev_text'          => '%title',
            'next_text'          => '%title',
            'in_same_term'       => false,
            'excluded_terms'     => '',
            'taxonomy'           => 'category',
            'screen_reader_text' => __( 'Post navigation' ),
        )
    );

    $navigation = '';

    $previous = get_previous_post_link(
        '<div class="col_half nobottommargin">%link</div>',
        $args['prev_text'],
        $args['in_same_term'],
        $args['excluded_terms'],
        $args['taxonomy']
    );

    $next = get_next_post_link(
        '<div class="col_half col_last tright nobottommargin">%link</div>',
        $args['next_text'],
        $args['in_same_term'],
        $args['excluded_terms'],
        $args['taxonomy']
    );

    // Only add markup if there's somewhere to navigate to.
    if ( $previous || $next ) {
        $navigation = _navigation_markup( $previous . $next, 'post-navigation', $args['screen_reader_text'] );
    }

    return $navigation;
}

function snth_comments_cb($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;

    snth_show_template('content/comments.php', array(
        'comment' => $comment,
        'args'    => $args,
        'depth'   => $depth,
    ) );
}

function snth_comments_cb_end($comment, $args, $depth) {
}

function snth_better_comments_cb($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;

    snth_show_template('content/better_comments.php', array(
        'comment' => $comment,
        'args'    => $args,
        'depth'   => $depth,
    ) );
}

function snth_default_image($size = 'full') {
    $default_images = get_field('default_images', 'options');
    $image = SNTH_IMAGES_URL . '/default_placeholder.jpg';

    if (!empty($default_images)) {
        $count = count($default_images);

        $rand = rand(0, $count - 1);

        $image_array = $default_images[$rand];

        if ('full' === $size) {
            $image = $image_array["image"]["url"];
        } else {
            $image = $image_array["image"]["sizes"][$size];

            if (empty($image)) {
                $image = $image_array["image"]["url"];
            }
        }
    }

    if (empty($image)) {
        $image = SNTH_IMAGES_URL . '/default_placeholder.jpg';
    }

    return $image;
}

function snth_comment_form_defaults($defaults) {
    $req      = get_option( 'require_name_email' );
    $commenter     = wp_get_current_commenter();
    $html_req = ( $req ? " required='required'" : '' );
    $html5    = false;

    $fields   = array(
        'author' => '<div class="comment-form-author col_one_third">' . '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
            '<input id="author" class="sm-form-control" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245"' . $html_req . ' /></div>',
        'email'  => '<div class="comment-form-email col_one_third"><label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
            '<input id="email" class="sm-form-control" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes"' . $html_req . ' /></div>',
        'url'    => '<div class="comment-form-url col_one_third  col_last"><label for="url">' . __( 'Website' ) . '</label> ' .
            '<input id="url" class="sm-form-control" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" /></div>',
    );

    $defaults['comment_field'] = '<div class="clear"></div><div class="col_full"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment"  name="comment" cols="58" rows="7" tabindex="4" class="sm-form-control" maxlength="65525" required="required"></textarea></div>';


    $defaults['cancel_reply_before'] = '';
    $defaults['cancel_reply_after'] = '';
    $defaults['fields'] = $fields;
    $defaults['class_submit'] = 'button button-3d nomargin';
    $defaults['class_form'] = 'clearfix';
    $defaults['title_reply_before'] = '<h3 id="reply-title" class="comment-reply-title">';
    $defaults['cancel_reply_after'] = '</h3>';

    return $defaults;
}

add_filter('comment_form_defaults', 'snth_comment_form_defaults', 15);

/**
 * Reorder form fields: Textarea is last
 *
 * @param $comment_fields
 * @return mixed
 */
function snth_comment_form_fields($comment_fields) {
    if (!empty($comment_fields['comment'])) {
        $comment_field = $comment_fields['comment'];
        unset($comment_fields['comment']);

        $comment_fields['comment'] = $comment_field;
    }

    return $comment_fields;
}

add_filter( 'comment_form_fields', 'snth_comment_form_fields' );
