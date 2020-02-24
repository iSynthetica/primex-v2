<?php
/**
 * Content templates functions
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function snth_add_cart_link( $items, $args )
{

    $currency = apply_filters( 'wcml_price_currency', NULL );

    if (!$currency) {
        $currency_label = __('uah', 'snthwp');
    } elseif ('EUR' === $currency) {
        $currency_label = '€';
    } elseif ('USD' === $currency) {
        $currency_label = '$';
    }

    $language = apply_filters( 'wpml_current_language', NULL );

    $language_label = strtoupper($language);

    ob_start();

    ?>
    <div class="desktop-dropdown-content">
    <?php
    echo do_shortcode('[wpml_language_switcher flags=1 native=1 translated=1][/wpml_language_switcher]');
    ?>
    </div>
    <?php

    $lang_switcher = ob_get_clean();

    ob_start();

    ?>
    <div class="desktop-dropdown-content">
    <?php
    echo do_shortcode('[currency_switcher format="%symbol%"]');
    ?>
    </div>
    <?php

    $cur_switcher = ob_get_clean();

    if($args->theme_location == 'main-nav')
    {

        // if( current_user_can('administrator') ) {
            $items .= '<li class="hidden-xs hidden-sm large-icons"><div class="desktop-dropdown"><i class="fas desktop-dropdown-control">'.$currency_label.'</i>' . $cur_switcher . '</div></li>';
            $items .= '<li class="hidden-xs hidden-sm large-icons"><div class="desktop-dropdown"><i class="fas desktop-dropdown-control">'.$language_label.'</i>' . $lang_switcher . '</div></li>';
        // }

        $items .= '<li class="hidden-xs hidden-sm large-icons"><a href="'. esc_url( wc_get_cart_url() ) .'"><i class="fas fa-shopping-basket"></i></a></li>';
    }

    if( is_user_logged_in() && current_user_can('administrator') ) {
        $items .= '<li class="large-icons"><a href="'. esc_url( admin_url() ) .'"><i class="fas fa-tachometer-alt"></i></a></li>';
    }

        return $items;
}
add_filter( 'wp_nav_menu_items', 'snth_add_cart_link', 10, 2);

/**
 * Display GoHome Logo link
 *
 * @param $logo
 * @param $args
 */
function snth_theme_logo_home_nav( $logo = '', $alt = '', $args = array() )
{
    $holder_id = !empty($args['holder_id']) ? ' id="'.$args['holder_id'].'"' : '';
    $holder_class = !empty($args['holder_class']) ? ' '.$args['holder_class'] : '';
    ?>
    <div<?php echo $holder_id ?> class="nav-logo-wrap local-scroll<?php echo $holder_class ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" rel="home"><?php
            if (snth_has_theme_logo($logo)) {
                ?><?php snth_theme_logo($logo, $alt); ?> <span><?php bloginfo( 'description' ) ?></span><?php
            } else {
                bloginfo( 'name' ) . ' | ' . bloginfo( 'description' );
            }
            ?></a>
    </div>
    <?php
}

/**
 * Check if theme has custom logo image
 *
 * @param string $logo
 *
 * @return bool
 */
function snth_has_theme_logo($logo = '') {
    return (bool) snth_get_theme_logo_src($logo);
}

/**
 * Display / Get Theme logo image
 *
 * @param string $logo
 * @param string $alt
 * @param bool $show
 *
 * @return string
 */
function snth_theme_logo($logo = '', $alt = '', $show = true) {
    $src = snth_get_theme_logo_src($logo);

    if(!$src) {
        $img = '';
    } else {
        $img = $image = '<img src="'.$src.'" alt="'.$alt.'">';
    }

    if (!$show) {
        return $image;
    }

    echo $image;
}

/**
 * Get Theme Logo src
 *
 * @param $logo
 *
 * @return bool|string
 */
function snth_get_theme_logo_src($logo) {
    $theme_logos = array_keys(apply_filters('snth_custom_logos', array()));

    if (!empty($theme_logos) && in_array($logo, $theme_logos) && $src = get_theme_mod( 'snth_'.$logo)) {
        return $src;
    }

    $theme_logo_id = get_theme_mod( 'custom_logo' );

    if(!$theme_logo_id) {
        return false;
    }

    $image = wp_get_attachment_image_src( $theme_logo_id , 'full' );

    return $image[0];

}

/**
 * Custom Breadcrumbs
 */
function snth_the_breadcrumbs() {
    /* === ОПЦИИ === */
    $text['home'] = __('Home', 'snthwp'); // текст ссылки "Главная"
    $text['category'] = '%s'; // текст для страницы рубрики
    $text['search'] = __('Search', 'snthwp'); // текст для страницы с результатами поиска
    $text['tag'] = __('Posts tagged with "%s"', 'snthwp'); // текст для страницы тега
    $text['author'] = __('Posts by %s', 'snthwp'); // текст для страницы автора
    $text['404'] = __('Error 404', 'snthwp'); // текст для страницы 404
    $text['page'] = __('Page %s', 'snthwp'); // текст 'Страница N'
    $text['cpage'] = __('Commentaries page %s', 'snthwp'); // текст 'Страница комментариев N'

    $wrap_before = '<div class="mod-breadcrumbs font-alt align-right" itemscope itemtype="http://schema.org/BreadcrumbList">'; // открывающий тег обертки
    $wrap_after = '</div><!-- .breadcrumbs -->'; // закрывающий тег обертки
    $sep = '/'; // разделитель между "крошками"
    $sep_before = '&nbsp'; // тег перед разделителем
    $sep_after = '&nbsp'; // тег после разделителя
    $show_home_link = 1; // 1 - показывать ссылку "Главная", 0 - не показывать
    $show_on_home = 0; // 1 - показывать "хлебные крошки" на главной странице, 0 - не показывать
    $show_current = 1; // 1 - показывать название текущей страницы, 0 - не показывать
    $before = '<span class="current">'; // тег перед текущей "крошкой"
    $after = '</span>'; // тег после текущей "крошки"
    /* === КОНЕЦ ОПЦИЙ === */

    global $post;
    $home_url = home_url('/');
    $link_before = '';
    $link_after = '';
    $link_attr = ' itemprop="item"';
    $link_in_before = '<span itemprop="name">';
    $link_in_after = '</span>';
    $link = $link_before . '<a href="%1$s"' . $link_attr . '>' . $link_in_before . '%2$s' . $link_in_after . '</a>' . $link_after;
    $frontpage_id = get_option('page_on_front');
    $parent_id = ($post) ? $post->post_parent : '';
    $sep = ' ' . $sep_before . $sep . $sep_after . ' ';
    $home_link = $link_before . '<a href="' . $home_url . '"' . $link_attr . '>' . $link_in_before . $text['home'] . $link_in_after . '</a>' . $link_after;

    if (is_home() || is_front_page()) {
        if ($show_on_home) echo $wrap_before . $home_link . $wrap_after;
    }
    else {
        echo $wrap_before;

        if ($show_home_link) {
            echo $home_link;
        }

        if ( is_category() ) {
            $cat = get_category(get_query_var('cat'), false);
            if ($cat->parent != 0) {
                $cats = get_category_parents($cat->parent, TRUE, $sep);
                $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
                if ($show_home_link) echo $sep;
                echo $cats;
            }
            if ( get_query_var('paged') ) {
                $cat = $cat->cat_ID;
                echo $sep . sprintf($link, get_category_link($cat), get_cat_name($cat)) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) echo $sep . $before . sprintf($text['category'], single_cat_title('', false)) . $after;
            }
        }
        elseif ( is_search() ) {
            if (have_posts()) {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . sprintf($text['search'], get_search_query()) . $after;
            } else {
                if ($show_home_link) echo $sep;
                echo $before . sprintf($text['search'], get_search_query()) . $after;
            }
        }
        elseif ( is_day() ) {
            if ($show_home_link) echo $sep;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $sep;
            echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F'));
            if ($show_current) echo $sep . $before . get_the_time('d') . $after;
        }
        elseif ( is_month() ) {
            if ($show_home_link) echo $sep;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'));
            if ($show_current) echo $sep . $before . get_the_time('F') . $after;
        }
        elseif ( is_year() ) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . get_the_time('Y') . $after;
        }
        elseif ( is_product_category() ) {
            global $wp_query;
            $show_parent = false;
            $product_cat_id = $wp_query->get_queried_object()->term_id;
            $cat = get_term($product_cat_id, 'product_cat', false);

            if ($show_home_link) {
                echo $sep;
            }

            // printf($link, get_permalink(wc_get_page_id( 'shop' )), get_the_title(wc_get_page_id( 'shop' )));

            //echo $sep;


            if ($show_parent) {
                if ( $cat ) {
                    $args = array(
                        'separator' => $sep,
                        'link'      => true,
                        'format'    => false,
                        'inclusive' => false,
                    );

                    $cats = get_term_parents_list( $cat, 'product_cat', $args );

                    if ( ! $show_current || get_query_var( 'cpage' ) ) {
                        $cats = preg_replace( "#^(.+)$sep$#", "$1", $cats );
                    }
                    $cats = preg_replace( '#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr . '>' . $link_in_before . '$2' . $link_in_after . '</a>' . $link_after, $cats );

                    echo $cats;
                }
            }

            if ( get_query_var('paged') ) {
                echo sprintf($link, get_term_link($cat, 'product_cat'), $cat->name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) echo $before . sprintf($text['category'], single_cat_title('', false)) . $after;
            }
        }
        elseif ( is_product() ) {
            if ($show_home_link) echo $sep;
            $show_parent = false;
            //printf($link, get_permalink(wc_get_page_id( 'shop' )), get_the_title(wc_get_page_id( 'shop' )));
            //echo $sep;

            $cat = get_post_meta($post->ID , '_yoast_wpseo_primary_product_cat', true);

            if (!$cat) {
                $cat = get_the_terms($post->ID, 'product_cat');
                $cat = $cat[0]->term_id;
            }

            if ($show_parent) {
                if ( $cat ) {
                    $args = array(
                        'separator' => $sep,
                        'link'      => true,
                        'format'    => false,
                    );

                    $cats = get_term_parents_list($cat, 'product_cat', $args);

                    if (!$show_current || get_query_var('cpage')) $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                    $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
                    echo $cats;
                }
            } else {
                $cat = get_term($cat, 'product_cat', false);
                $cat_link = get_term_link( $cat, "product_cat" );
                echo $before . '<a href="'.$cat_link.'">'.$cat->name.'</a>' . $after . $sep;
            }


            if ( get_query_var('cpage') ) {
                echo $sep . sprintf($link, get_permalink(), snth_get_short_title()) . $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . $after;
            } else {
                if ($show_current) echo $before . snth_get_short_title() . $after;
            }
        }
        elseif ( is_single() && get_post_type() === 'program' ) {
            if ($show_home_link) echo $sep;
            $post_type_obj = get_post_type_object('program');
            $slug = $post_type_obj->rewrite;

            $archive_title = get_field('program_archive_title', 'options');

            printf($link, $home_url . $slug['slug'] . '/', $archive_title);

            $cat = get_post_meta($post->ID , '_yoast_wpseo_primary_program_cat', true);

            if (!$cat) {
                $cat = get_the_terms($post->ID, 'program_cat'); $cat = $cat[0];
            }

            if($cat) {
                $args = array(
                    'separator' => $sep,
                    'link'      => true,
                    'format'    => false,
                );

                $cats = get_term_parents_list($cat, 'program_cat', $args);

                if (!$show_current || get_query_var('cpage')) {
                    $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                }

                $cats = preg_replace(
                    '#<a([^>]+)>([^<]+)<\/a>#',
                    $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after,
                    $cats
                );

                echo $sep . $cats;
            }

            if ($show_current) echo $before . snth_get_short_title() . $after;

        }
        elseif ( is_single() && !is_attachment() ) {
            if ($show_home_link) echo $sep;
            if ( get_post_type() != 'post' ) {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                printf($link, $home_url . $slug['slug'] . '/', $post_type->labels->singular_name);
                if ($show_current) echo $sep . $before . snth_get_short_title() . $after;
            } else {
                $cat = get_post_meta($post->ID , '_yoast_wpseo_primary_category', true);
                if (!$cat) {
                    $cat = get_the_category(); $cat = $cat[0];
                }

                $cats = get_category_parents($cat, TRUE, $sep);

                if (!$show_current || get_query_var('cpage')) $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);

                // echo $cats;

                if ( get_query_var('cpage') ) {
                    echo $sep . sprintf($link, get_permalink(), snth_get_short_title()) . $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . $after;
                } else {
                    if ($show_current) echo $before . snth_get_short_title() . $after;
                }
            }
        }
        elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
            $post_type = get_post_type_object(get_post_type());
            if ( get_query_var('paged') ) {
                echo $sep . sprintf($link, get_post_type_archive_link($post_type->name), $post_type->label) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) echo $sep . $before . $post_type->label . $after;
            }

        }
        elseif ( is_attachment() ) {
            if ($show_home_link) echo $sep;
            $parent = get_post($parent_id);
            $cat = get_the_category($parent->ID); $cat = $cat[0];
            if ($cat) {
                $cats = get_category_parents($cat, TRUE, $sep);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
                echo $cats;
            }
            printf($link, get_permalink($parent), $parent->post_title);
            if ($show_current) echo $sep . $before . get_the_title() . $after;

        }
        elseif ( is_page() && !$parent_id ) {
            if ($show_current) echo $sep . $before . snth_get_short_title() . $after;
        }
        elseif ( is_page() && $parent_id ) {
            if ($show_home_link) echo $sep;

            if ($parent_id != $frontpage_id) {
                $breadcrumbs = array();
                while ($parent_id) {
                    $page = get_page($parent_id);

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
            }

            if ($show_current) {
                echo $sep . $before . snth_get_short_title() . $after;
            }
        }
        elseif ( is_tag() ) {
            if ( get_query_var('paged') ) {
                $tag_id = get_queried_object_id();
                $tag = get_tag($tag_id);
                echo $sep . sprintf($link, get_tag_link($tag_id), $tag->name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) echo $sep . $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
            }

        }
        elseif ( is_author() ) {
            global $author;
            $author = get_userdata($author);
            if ( get_query_var('paged') ) {
                if ($show_home_link) echo $sep;
                echo sprintf($link, get_author_posts_url($author->ID), $author->display_name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . sprintf($text['author'], $author->display_name) . $after;
            }

        }
        elseif ( is_404() ) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . $text['404'] . $after;

        }
        elseif ( has_post_format() && !is_singular() ) {
            if ($show_home_link) echo $sep;
            echo get_post_format_string( get_post_format() );
        }

        echo $wrap_after;
    }
} // end of dimox_breadcrumbs()

function snth_get_short_title($id = false)
{
    if (!$id) {
        if (!is_singular()) {
            return '';
        }
        global $post;
        $id = $post->ID;
    }

    $short_title = get_field('short_title', $id);

    if ($short_title) {
        return $short_title;
    }

    return get_the_title($id);
}

function snth_get_sub_title($id = false)
{
    if (!$id) {
        if (!is_singular()) {
            return '';
        }
        global $post;
        $id = $post->ID;
    }

    $short_title = get_field('subtitle', $id);

    if ($short_title) {
        return $short_title;
    }

    return false;
}

/**
 * Display before main content
 *
 * @param $template
 * @param $sidebar
 */
function snth_before_main_content($template, $sidebar = '')
{
    if ('no-sidebar' === $template) {
        return;
    }

    if ('sidebar-fullwidth' === $template) {
        $main_class = 'col-sm-10 col-sm-offset-1';
        $secondary_class = '';
    }

    if ('sidebar-left' === $template) {
        $main_class = 'col-sm-8 col-sm-push-4';
        $secondary_class = '';
    }

    if ('sidebar-right' === $template) {
        $main_class = 'col-sm-8';
        $secondary_class = '';
    }
    ?>
    <section class="page-section pt-80 pt-xs-30 pb-50 pb-xs-20">
        <div class="container relative">
            <div class="row">
                <div class="<?php echo $main_class ?>">
    <?php
}

/**
 * Display after main content
 *
 * @param $template
 * @param $sidebar
 */
function snth_after_main_content($template, $sidebar = '')
{
    if ('no-sidebar' === $template) {
        return;
    }

    if ('sidebar-fullwidth' === $template) {
        $main_class = '';
        $secondary_class = 'col-sm-10 col-sm-offset-1';
    }

    if ('sidebar-left' === $template) {
        $main_class = '';
        $secondary_class = 'col-sm-4 col-md-3 col-sm-pull-8';
    }

    if ('sidebar-right' === $template) {
        $main_class = '';
        $secondary_class = 'col-sm-4 col-md-3 col-md-offset-1';
    }
    ?>
                </div>

                <!-- Sidebar -->
                <div class="<?php echo $secondary_class ?>">
                    <aside id="secondary" class="widget-area" role="complementary">
                        <?php snth_show_template('sidebar/'.$sidebar.'.php') ?>
                    </aside>
                </div>
                <!-- End Sidebar -->
            </div>
        </div>
    </section>
    <?php
}

function snth_show_social_share() {
    snth_show_template('blocks/share.php');
}

function snth_get_map_markers( $type = false ) {

    $locations = snth_get_locations( $type );

    $map_markers = array();

    foreach($locations as $location){

        $marker = get_field('marker', $location->ID);
        $is_center = get_field('is_center', $location->ID);

        if($is_center) {
            $map_markers['center'] = array(
                'lat' => $marker['lat'],
                'lng' => $marker['lng'],
            );
        }

        ob_start();
        ?>
        <div class="location-info">
            <div class="location-info__inner">
                <header class="location-info__header">
                    <h3><?php echo __($location->post_title, 'snthwp') ?></h3>
                </header>

                <div class="location-info__body">
                    <p>
                        <strong><?php echo __('Address', 'snthwp') ?>:</strong>
                    </p>

                    <p>
                        <?php echo get_field('country', $location->ID); ?>,
                        <?php echo get_field('city', $location->ID); ?>,
                        <?php echo get_field('address', $location->ID); ?>
                    </p>

                    <p>
                        <strong><?php echo __('Phone', 'snthwp') ?>:</strong>
                        <?php echo wpautop(get_field('phone', $location->ID)); ?>
                    </p>

                    <p>
                        <strong><?php echo __('Schedule', 'snthwp') ?>:</strong>
                        <?php echo wpautop(get_field('schedule', $location->ID)); ?>
                    </p>
                </div>

                <footer class="location-info__footer">

                </footer>
            </div>
        </div>
        <?php
        $info = ob_get_clean();

        $map_markers[$location->ID] = array(
            'marker' => array(
                'lat' => $marker['lat'],
                'lng' => $marker['lng'],
            ),
            'title' => $location->post_title,
            'info'   =>  $info,
        );
    }

    return $map_markers;
}

function snth_get_locations( $type = false ) {

    $args = array(
        'numberposts' => -1,
        'category'    => 0,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'include'     => array(),
        'exclude'     => array(),
        'meta_key'    => '',
        'meta_value'  =>'',
        'post_type'   => 'location',
        'suppress_filters' => false, // подавление работы фильтров изменения SQL запроса
    );

    if ( $type ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'location_type',
                'field'    => 'slug',
                'terms'    => $type
            )
        );
    }

    return get_posts( $args );
}

function snth_before_home_section_content( $content ) {
    $bg_color = $content['section_background'] ? ' ' . $content['section_background'] : '';
    ?>
    <section class="page-section section-one_col pt-30 pb-30 pt-xs-10 pb-xs-10<?php echo $bg_color ?>" id="<?php echo $content['section_id'] ?>">
        <div class="container relative">

            <?php snth_show_template('sections/home-section-title.php', array('content' => $content)); ?>

            <div class="section-text section-content">
    <?php
}

function snth_after_home_section_content() {
    ?>

            </div>
        </div>
    </section>
    <?php
}
