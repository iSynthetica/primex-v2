<?php
/**
 * Navigation Menus
 *
 * @package Hooka/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registering navigation menu
 */
register_nav_menus(
    array(
        'main-nav'      => __( 'Main Menu Navigation', 'snthwp' ),
        'footer-nav'    => __( 'Footer Navigation', 'snthwp' )
    )
);

/**
 * The Main Menu
 */
function snth_main_nav()
{
    wp_nav_menu(array(
        'container' => false,
        'menu_class' => 'nav navbar-nav no-margin alt-font text-normal',
        'items_wrap' => '<ul>%3$s</ul>',
        'theme_location' => 'main-nav',
        'depth' => 3,
        'fallback_cb' => false,
        'walker' => new Main_Nav_Walker()
    ));
}

/**
 * Class Mobile_Main_Menu_Walker
 *
 * Make accordion parent element clickable
 * by adding <span> wrapper around parent link
 * and <a> tag for expanding menu
 */
class Main_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );

        // Default class.
        $classes = array( 'dropdown-menu' );

        $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
        $class_names = '';
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $output .= "{$n}{$indent}<ul$class_names>{$n}";
    }

    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        if (in_array('hide-for-desktop', $classes)) {
            $class_names = ' class="hidden-lg"';
        } else {

            //TODO - Remove
            $class_names = '';
        }

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $local_before = '';
        $local_after = '';
        $local_link_after = '';
        $local_link_class = '';

        if (in_array('menu-item-has-children', $classes)) {
//            if (0 === $depth) {
//                $class_names = ' class="dropdown simple-dropdown"';
//                $local_after .= ' <i class="fas fa-angle-down dropdown-toggle" data-toggle="dropdown" aria-hidden="true"></i>';
//            }
//
//            if (1 === $depth) {
//                $class_names = ' class="dropdown"';
//                $local_link_after .= ' <i class="fas fa-angle-right"></i>';
//                $local_link_class .= 'dropdown-toggle';
//            }
        }

        $output .= $indent . '<li' . $id . $class_names .'>';

        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        $atts['class']  = $local_link_class;
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        /** This filter is documented in wp-includes/post-template.php */
        $title = apply_filters( 'the_title', $item->title, $item->ID );
        $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

        $item_output = $local_before . $args->before;
        $item_output .= '<a'. $attributes .'><div>';
        $item_output .= $args->link_before . $title . $local_link_after . $args->link_after;
        $item_output .= '</div></a>';
        $item_output .= $args->after . $local_after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}

/**
 * The Main Menu
 */
function snth_main_nav_01()
{
    wp_nav_menu(array(
        'container' => false,
        'menu_class' => 'menu clearlist',
        'items_wrap' => '<ul id="%1$s" class="%2$s" data-dropdown-menu data-close-on-click-inside="false">%3$s</ul>',
        'theme_location' => 'main-nav',
        'depth' => 3,
        'fallback_cb' => false,
        'walker' => new Main_Nav_Walker()
    ));
}

/**
 * Class Mobile_Main_Menu_Walker
 *
 * Make accordion parent element clickable
 * by adding <span> wrapper around parent link
 * and <a> tag for expanding menu
 */
class Main_Nav_Walker_01 extends Walker_Nav_Menu {
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );

        // Default class.
        $classes = array( 'mn-sub sub-menu' );

        $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $output .= "{$n}{$indent}<ul$class_names>{$n}";
    }

    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        if (in_array('hide-for-desktop', $classes)) {
            $class_names = ' class="hidden-lg"';
        } else {

            //TODO - Remove
            $class_names = '';
        }

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $local_link_after = '';
        $local_link_class = '';

        if (in_array('menu-item-has-children', $classes)) {
            if (0 === $depth) {
                $local_link_after .= ' <i class="fa fa-angle-down"></i>';
            }

            if (1 === $depth) {
                $local_link_after .= ' <i class="fa fa-angle-right right"></i>';
            }

            $local_link_class .= 'mn-has-sub';
        }

        $output .= $indent . '<li' . $id . $class_names .'>';

        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        $atts['class']  = $local_link_class;
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        /** This filter is documented in wp-includes/post-template.php */
        $title = apply_filters( 'the_title', $item->title, $item->ID );
        $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . $title . $local_link_after . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}
