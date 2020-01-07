<?php
/**
 *
 */

echo 'Comment form';
/* == Options - Start == */
$text['home'] = __('Home', 'primex');
$text['blog'] = __('Blog', 'primex');
$text['category'] = '%s';
$text['tag'] = __('Posts tagged with "%s"', 'primex');
$text['page'] = __('Page %s', 'primex');

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
?>

