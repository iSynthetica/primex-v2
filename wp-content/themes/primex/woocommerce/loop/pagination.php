<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
    return;
}

$paginate_links = paginate_links( array( // WPCS: XSS ok.
    'base'         => $base,
    'current'      => max( 1, $current ),
    'total'        => $total,
    'end_size' => 1,
    'mid_size' => 2,
    'prev_next' => true,
    'prev_text' => __( '<i class="fas fa-angle-left"></i>', 'primex' ),
    'next_text' => __( '<i class="fas fa-angle-right"></i>', 'primex' ),
    'type'         => 'array',
) );

if (!empty($paginate_links)) {
    foreach ($paginate_links as $key => $link) {
        $new_link = str_replace('<a class="prev page-numbers"', '<li class="page-item"><a class="page-link"', $link);
        $new_link = str_replace('<a class="next page-numbers"', '<li class="page-item"><a class="page-link"', $new_link);
        $new_link = str_replace('<a class="page-numbers"', '<li class="page-item"><a class="page-link"', $new_link);
        $new_link = str_replace('<a class=\'page-numbers\'', '<li class="page-item"><a class="page-link"', $new_link);
        $new_link = str_replace('<span class="page-numbers dots"', '<li class="page-item disabled"><span class="page-link"', $new_link);
        $new_link = str_replace('<span aria-current="page" class="page-numbers current"', '<li class="page-item active"><span class="page-link"', $new_link);
        $new_link = str_replace('</a>', '</a></li>', $new_link);
        $new_link = str_replace('</span>', '</span></li>', $new_link);

        $paginate_links[$key] = $new_link;
    }
}

if ( $paginate_links ) {
    echo '<nav aria-label="Page navigation" class="page-navigation woocommerce-pagination topmargin-sm">';
    echo '<ul class="pagination justify-content-center pagination-rounded ">';
    foreach ($paginate_links as $link) {
        echo $link;
    }
    echo '</ul>';
    echo '</nav><!--// end .pagination -->';
}
?>
