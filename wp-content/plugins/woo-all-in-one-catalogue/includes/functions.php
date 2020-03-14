<?php
/**
 * Helpers library functions
 *
 * @since      0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * wooaioc_is_plugin_activated.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wooaioc_is_plugin_activated( $plugin_folder, $plugin_file ) {
    if ( wooaioc_is_plugin_active_simple( $plugin_folder . '/' . $plugin_file ) ) {
        return true;
    } else {
        return wooaioc_is_plugin_active_by_file( $plugin_file );
    }
}

/**
 * wooaioc_is_plugin_active_simple.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wooaioc_is_plugin_active_simple( $plugin ) {
    return (
        in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
        ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
    );
}

/**
 * wooaioc_is_plugin_active_by_file.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wooaioc_is_plugin_active_by_file( $plugin_file ) {
    foreach ( wooaioc_get_active_plugins() as $active_plugin ) {
        $active_plugin = explode( '/', $active_plugin );

        if ( isset( $active_plugin[1] ) && $plugin_file === $active_plugin[1] ) {
            return true;
        }
    }

    return false;
}

/**
 * wprshrtcd_get_active_plugins.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  array
 */
function wooaioc_get_active_plugins() {
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

    if ( is_multisite() ) {
        $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
    }

    return $active_plugins;
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function wooaioc_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    $template = wooaioc_locate_template( $template_name, $template_path, $default_path );

    do_action( 'wooaioc_before_template_part', $template_name, $template_path, $args );

    if (!empty($args) && is_array($args)) {
        extract($args);
    }

    include $template;

    do_action( 'wooaioc_after_template_part', $template_name, $template_path, $args );
}

/**
 * Like wc_get_template, but returns the HTML instead of outputting.
 *
 * @see wc_get_template
 * @since 2.5.0
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function wooaioc_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    ob_start();

    wooaioc_get_template( $template_name, $args, $template_path, $default_path );

    return ob_get_clean();
}


/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function wooaioc_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = 'woocommerce/woo-all-in-one-catalogue';
    }

    if ( ! $default_path ) {
        $default_path = untrailingslashit( plugin_dir_path( WOOAIOCATALOGUE_FILE ) ) . '/templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    // Get default template/.
    if ( ! $template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return $template;
}

function wooaioc_get_product_categories_tree($parent = 0) {
    $tree = array();

    $next = get_terms(array(
        'taxonomy' => 'product_cat',
        'parent' => $parent
    ));

    if (!empty($next)) {
        foreach ($next as $cat) {
            $tree[$cat->term_id] = array(
                'category' => $cat,
                'children' => wooaioc_get_product_categories_tree($cat->term_id)
            );

            $products_args = array(
                'numberposts' => '-1',
                'orderby'     => 'date',
                'order'       => 'DESC',
                'post_type'   => 'product',
                'suppress_filters' => false,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $cat->term_id,
                        'operator' => 'IN',
                        "include_children" => false
                    )
                ),
            );

            $products = wc_get_products($products_args);

            if (!empty($products)) {
                $tree[$cat->term_id]['products'] = array();

                foreach ($products as $product) {
                    $product->catalogue_price_html = $product->get_price_html();
                    $product_type = $product->get_type();

                    $stored_product = array(
                        'id' => $product->get_id(),
                        'catalogue_price_html' => $product->get_price_html(),
                    );

                    $tree[$cat->term_id]['products'][$product->get_id()] = $stored_product;

                    if ('variable' === $product_type) {
                        $available_variations = $product->get_available_variations();
                        if (!empty($available_variations)) {
                            foreach ($available_variations as $variation) {
                                $variation_product = wc_get_product($variation['variation_id']);
                                $variation_product->catalogue_price_html = $variation_product->get_price_html();

                                $stored_product = array(
                                    'id' => $variation_product->get_id(),
                                    'catalogue_price_html' => $variation_product->get_price_html(),
                                );

                                $tree[$cat->term_id]['products'][$variation_product->get_id()] = $stored_product;
                            }
                        }
                    }
                }
            }
        }
    }

    return $tree;
}

function wooaioc_display_catalogue_item($item, $depth = 0) {
    ?>
    <tr>
        <th colspan="6">
            <h4><?php echo $item['category']->name; ?></h4>
        </th>
    </tr>
    <?php
    if (!empty($item['products'])) {
        ?>
            <?php
            foreach ($item['products'] as $product_id => $stored_product) {
                $product = wc_get_product($product_id);

                $product_data = $product->get_data();
                $product_type = $product->get_type();
                ?>
                <tr>
                    <td class="product-thumbnail" style="min-width: 100px;">
                        <?php
                        if ( '' !== get_the_post_thumbnail($product_data['id']) ) {
                            ?>
                            <a href="<?php echo esc_url( get_permalink($product_data['id']) ); ?>">
                                <img class="image_fade" src="<?php echo get_the_post_thumbnail_url( $product_data['id'], 'thumbnail' ); ?>" alt="<?php echo $product_data['name'] ?>" style="max-height: 45px;width: auto;">
                            </a>
                            <?php
                        }
                        ?>
                    </td>

                    <td class="product-name responsive-no-title" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                        <a href="<?php echo esc_url( get_permalink($product_data['id']) ); ?>">
                            <?php echo $product->get_name() ?>
                        </a>
                    </td>
                    <td class="product-short-description responsive-hide">
                        <?php do_action('wooaioc_display_catalogue_item_description', $product); ?>
                    </td>
                    <td class="product-price responsive-border" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
                        <?php echo $stored_product['catalogue_price_html']; ?>
                    </td>
                    <td class="product-quantity responsive-border" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                        <?php
                        if ('variable' !== $product_type) {
                            ?>
                            <div class="quantity clearfix">
                                <input type="button" value="-" class="minus">
                                <input type="number" min="0" max=""step="1" size="4" class="catalogue-item-qty input-text qty text" inputmode="numeric" value="1">
                                <input type="button" value="+" class="plus">
                            </div>
                            <?php
                        }
                        ?>
                    </td>
                    <td class="product-catalogue-add-to-cart responsive-no-title responsive-border">
                        <?php
                        if ('variable' !== $product_type) {
                            do_action('wooaioc_display_catalogue_item_add_to_cart', $product);
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        <?php
    }
    if (!empty($item['children'])) {
        foreach ($item['children'] as $children_item) {
            wooaioc_display_catalogue_item($children_item, $depth++);
        }
    }
}

function wooaioc_get_columns_catalogue_item() {
    return array(
            'A' => array(
                'width' => '8',
                'title' => __('SKU', 'woo-all-in-one-catalogue'),
                'field' => 'sku',
            ),
            'B' => array(
                'width' => '32',
                'title' => __('Product Title', 'woo-all-in-one-catalogue'),
                'field' => 'name',
            ),
            'C' => array(
                'width' => '55',
                'title' => __('Product Description', 'woo-all-in-one-catalogue'),
                'field' => 'description',
            ),
            'D' => array(
                'width' => '16',
                'title' => __('Price', 'woo-all-in-one-catalogue') . ' ' . get_woocommerce_currency_symbol(),
                'field' => 'price',
                'format' => 'money'
            ),
            'E' => array(
                'width' => '16',
                'title' => __('Wholesale Price', 'woo-all-in-one-catalogue') . ' ' . get_woocommerce_currency_symbol(),
                'field' => 'wholesale_price',
            ),
    );
}

function wooaioc_add_row_catalogue_item($item, $spreadsheet, $row) {

    $columns = wooaioc_get_columns_catalogue_item();
    $columns_letters = array_keys($columns);
    $first_letter = $columns_letters[0];
    $last_letter = end($columns_letters);
    reset($columns_letters);

    $spreadsheet->getActiveSheet()->setCellValue($first_letter . $row, $item['category']->name)
                ->mergeCells($first_letter.$row.':'.$last_letter.$row)->getStyle($first_letter . $row)->applyFromArray(wooaioc_get_row_style('parent_category'));
    $row++;
    if (!empty($item['products'])) {
        foreach ($columns as $letter => $column) {
            $cell = $letter.$row;
            $spreadsheet->getActiveSheet()->setCellValue($cell, $column['title'])
                ->getStyle($cell)->applyFromArray(wooaioc_get_row_style('product_table_header'));
        }

        $row++;

        foreach ($item['products'] as $product) {
            $spreadsheet = wooaioc_get_row_product_item($product, $spreadsheet, $row);
            $spreadsheet->getActiveSheet()->getStyle($first_letter.$row.':'.$last_letter.$row)->getAlignment()->setWrapText(true);
            $row++;
        }
    }

    if (!empty($item['children'])) {
        foreach ($item['children'] as $children_item) {
            $result = wooaioc_add_row_catalogue_item($children_item, $spreadsheet, $row);
            $spreadsheet = $result['spreadsheet'];
            $row = $result['row'];
        }
    }

    return array(
        'spreadsheet' => $spreadsheet,
        'row' => $row
    );
}

function wooaioc_get_row_product_item($product, $spreadsheet, $row) {
    $columns = wooaioc_get_columns_catalogue_item();
    $columns_letters = array_keys($columns);
    $first_letter = $columns_letters[0];
    $last_letter = end($columns_letters);
    reset($columns_letters);

    foreach ($columns as $letter => $column) {
        $cell = $letter.$row;
        $value = wooaioc_get_product_item_value($product, $column['field']);
        $spreadsheet->getActiveSheet()->setCellValue($cell, $value)
                    ->getStyle($cell)->applyFromArray(wooaioc_get_row_style('product_table_body'));
    }

    return $spreadsheet;
}

function wooaioc_get_product_item_value($product, $field) {
    $product_data = $product->get_data();

    switch ($field) {
        case 'sku':
            return $product_data['sku'];
        case 'name':
            return $product_data['name'];
        case 'description':
            return $product_data['description'];
        case 'price':
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $price = $product->get_price();
            return $price;
        default:
            return '';
    }
}

function wooaioc_get_row_style($type) {
    $style = array(
        'parent_category' => array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ),
        ),
        'product_table_header' => array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'EEEEEE',
                )
            ),
            'borders' => array(
                'top' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
                'right' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
                'bottom' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
                'left' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
            ),
        ),
        'product_table_body' => array(
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            ),
            'borders' => array(
                'top' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
                'right' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
                'bottom' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
                'left' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '333333',
                    )
                ),
            ),
        ),
    );

    if (!empty($style[$type])) {
        return $style[$type];
    }

    return array();
}

add_action('init', 'wooaioc_rewrite_rule');

function wooaioc_rewrite_rule() {
    add_rewrite_rule(
        '^wooaioc-download-catalogue/(.*)/?',
        'index.php?file_format=$matches[1]',
        'top'
    );

    add_rewrite_tag('%file_format%', '(.*)');
}

function wooaioc_download_catalogues($item, $depth = 0) {
    $file_format = get_query_var('file_format');

    if ($file_format) {
        include WOOAIOCATALOGUE_PATH . '/parts/download.php';

        die;
    }
}
add_action('template_redirect', 'wooaioc_download_catalogues');

function wooaioc_add_to_cart() {
    ob_start();

    // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
    if ( ! isset( $_POST['product_id'] ) ) {
        return;
    }

    $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
    $product           = wc_get_product( $product_id );
    $quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
    $product_status    = get_post_status( $product_id );
    $variation_id      = 0;
    $variation         = array();

    if ( $product && 'variation' === $product->get_type() ) {
        $variation_id = $product_id;
        $product_id   = $product->get_parent_id();
        $variation    = $product->get_variation_attributes();
    }

    if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {

        do_action( 'woocommerce_ajax_added_to_cart', $product_id );

        if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
            wc_add_to_cart_message( array( $product_id => $quantity ), true );
        }

        WC_AJAX::get_refreshed_fragments();

    } else {

        // If there was an error adding to the cart, redirect to the product page to show any errors.
        $data = array(
            'error'       => true,
            'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
        );

        wp_send_json( $data );
    }
    // phpcs:enable
}

add_action('wp_ajax_nopriv_wooaioc_add_to_cart', 'wooaioc_add_to_cart');
add_action('wp_ajax_wooaioc_add_to_cart', 'wooaioc_add_to_cart');

function wooaioc_load_global_currency_rule($load) {
    return true;
}

function wooaioc_load_catalogue() {
    $catalogue_tree = get_transient('wooaiocatalogue_catalogue_tree');

    $download_catalogue_label = apply_filters('wooaioc_download_catalogue_label', __('Download catalogue', 'woo-all-in-one-catalogue'));

    ob_start();
    if (!empty($catalogue_tree)) {
        ?>
        <div class="catalogue-action-container">
            <a href="<?php echo get_home_url() . '/wooaioc-download-catalogue/excel'; ?>" class="button"><?php echo $download_catalogue_label; ?></a>
        </div>
        <div class="catalogue-container">
            <table id="wooaioc-catalogue-table" class="wooaioc-catalogue-table shop_table table table-hover table-sm shop_table_responsive">
                <?php

                foreach ($catalogue_tree as $catalogue_item) {
                    wooaioc_display_catalogue_item($catalogue_item);
                }

                ?>
            </table>
        </div>
        <div class="catalogue-action-container">
            <a href="<?php echo get_home_url() . '/wooaioc-download-catalogue/excel'; ?>" class="button"><?php echo $download_catalogue_label; ?></a>
        </div>
        <?php
    } else {
        ?>
        <div class="catalogue-container">
            <h3><?php echo __('No products found', 'woo-all-in-one-catalogue'); ?></h3>
        </div>
        <?php
    }
    $html = ob_get_clean();

    $data = array(
        'success' => true,
        'html' => $html,
    );

    wp_send_json( $data );
}

add_action('wp_ajax_nopriv_wooaioc_load_catalogue', 'wooaioc_load_catalogue');
add_action('wp_ajax_wooaioc_load_catalogue', 'wooaioc_load_catalogue');
