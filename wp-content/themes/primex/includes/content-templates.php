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

function snth_pagination() {
    global $wp_query;
    $big = 999999999;

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
        echo '<ul class="pagination justify-content-center">';
        foreach ($paginate_links as $link) {
            echo $link;
        }
        echo '</ul>';
        echo '</nav><!--// end .pagination -->';
    }
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
//    $defaults = array(
//        /** This filter is documented in wp-includes/link-template.php */
//        'must_log_in'          => '<p class="must-log-in">' . sprintf(
//            /* translators: %s: login URL */
//                __( 'You must be <a href="%s">logged in</a> to post a comment.' ),
//                wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ) )
//            ) . '</p>',
//        /** This filter is documented in wp-includes/link-template.php */
//        'logged_in_as'         => '<p class="logged-in-as">' . sprintf(
//            /* translators: 1: edit user link, 2: accessibility text, 3: user name, 4: logout URL */
//                __( '<a href="%1$s" aria-label="%2$s">Logged in as %3$s</a>. <a href="%4$s">Log out?</a>' ),
//                get_edit_user_link(),
//                /* translators: %s: user name */
//                esc_attr( sprintf( __( 'Logged in as %s. Edit your profile.' ), $user_identity ) ),
//                $user_identity,
//                wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ) )
//            ) . '</p>',
//        'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . __( 'Your email address will not be published.' ) . '</span>' . ( $req ? $required_text : '' ) . '</p>',
//        'comment_notes_after'  => '',
//        'action'               => site_url( '/wp-comments-post.php' ),
//        'id_form'              => 'commentform',
//        'id_submit'            => 'submit',
//        'name_submit'          => 'submit',
//        'title_reply'          => __( 'Leave a Reply' ),
//        'title_reply_to'       => __( 'Leave a Reply to %s' ),
//        'cancel_reply_before'  => ' <small>',
//        'cancel_reply_after'   => '</small>',
//        'cancel_reply_link'    => __( 'Cancel reply' ),
//        'label_submit'         => __( 'Post Comment' ),
//        'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
//        'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
//        'format'               => 'xhtml',
//    );

    $defaults['comment_field'] = '<div class="clear"></div><div class="col_full"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment"  name="comment" cols="58" rows="7" tabindex="4" class="sm-form-control" maxlength="65525" required="required"></textarea></div>';

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
