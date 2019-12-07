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

function wooaioc_get_product_categories_tree1() {
    global $wpdb;
    $sql = "
SELECT * FROM {$wpdb->terms} AS t
JOIN {$wpdb->term_taxonomy} AS tt
ON t.`term_id` = tt.`term_id`
WHERE tt.`taxonomy` = 'product_cat'
    ";

    $results = $wpdb->get_results($sql, ARRAY_A);
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
                    $tree[$cat->term_id]['products'][$product->get_id()] = $product;
                }
            }
        }
    }

    return $tree;
}

function wooaioc_display_catalogue_item($item, $depth = 0) {
    ?>
    <li>
        <h4><?php echo $item['category']->name; ?></h4>
        <?php
        if (!empty($item['products'])) {
            ?>
            <table class="wooaioc-catalogue-table">
                <?php
                foreach ($item['products'] as $product) {
                    $product_data = $product->get_data();
                    ?>
                    <tr>
                        <td>
                            <div style="padding:1px 5px;">
                                <?php
                                if ( '' !== get_the_post_thumbnail($product_data['id']) ) {
                                    ?>
                                    <a href="<?php echo esc_url( get_permalink($product_data['id']) ); ?>">
                                        <img class="image_fade" src="<?php echo get_the_post_thumbnail_url( $product_data['id'], 'thumbnail' ); ?>" alt="<?php echo $product_data['name'] ?>" style="max-height: 60px;width: auto;">
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <div style="padding:1px 5px;display: inline-block;vertical-align: middle;">
                                <a href="<?php echo esc_url( get_permalink($product_data['id']) ); ?>">
                                    <?php echo $product_data['name'] ?>
                                </a>
                                <?php
                                // var_dump($product_data);
                                ?>
                            </div>
                        </td>
                        <td>
                            <div style="padding:1px 5px;display: inline-block;vertical-align: middle;">
                                <button class="button" type="button">
                                    <?php _e('Description', 'woo-all-in-one-catalogue'); ?>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div style="padding:1px 5px;display: inline-block;vertical-align: middle;">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                        </td>
                        <td>
                            <div style="padding:1px 5px;display: inline-block;vertical-align: middle;">
                                <input type="number" min="0" class="catalogue-item-qty" style="max-width:60px;">
                            </div>
                        </td>
                        <td>
                            <div style="padding:1px 5px;display: inline-block;vertical-align: middle;">
                                <button class="button" type="button">
                                    <?php _e('Add to cart', 'woo-all-in-one-catalogue'); ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
        if (!empty($item['children'])) {
            ?>
            <ul>
                <?php
                foreach ($item['children'] as $children_item) {
                    wooaioc_display_catalogue_item($children_item, $depth++);
                }
                ?>
            </ul>
            <?php
        }
        ?>
    </li>
    <?php
}

function wooaioc_get_columns_catalogue_item() {
    return array(
            'A' => array(
                    'width' => '8',
            ),
            'B' => array(
                'width' => '32',
            ),
            'C' => array(
                'width' => '55',
            ),
            'D' => array(
                'width' => '16',
            ),
            'E' => array(
                'width' => '16',
            ),
    );
}

function wooaioc_add_row_catalogue_item($item, $spreadsheet, $row) {
    $spreadsheet->getActiveSheet()->setCellValue('A' . $row, $item['category']->name)
                ->mergeCells('A'.$row.':E'.$row)->getStyle('A' . $row)->applyFromArray(wooaioc_get_row_style('parent_category'));
    $row++;
    if (!empty($item['products'])) {
        $spreadsheet->getActiveSheet()->setCellValue('A' . $row, __('SKU', 'woo-all-in-one-catalogue'))
                    ->getStyle('A'.$row)->applyFromArray(wooaioc_get_row_style('product_table_header'));
        $spreadsheet->getActiveSheet()->setCellValue('B' . $row, __('Product Title', 'woo-all-in-one-catalogue'))
                    ->getStyle('B'.$row)->applyFromArray(wooaioc_get_row_style('product_table_header'));
        $spreadsheet->getActiveSheet()->setCellValue('C' . $row, __('Product Description', 'woo-all-in-one-catalogue'))
                    ->getStyle('C'.$row)->applyFromArray(wooaioc_get_row_style('product_table_header'));
        $spreadsheet->getActiveSheet()->setCellValue('D' . $row, __('Price', 'woo-all-in-one-catalogue'))
                    ->getStyle('D'.$row)->applyFromArray(wooaioc_get_row_style('product_table_header'));
        $spreadsheet->getActiveSheet()->setCellValue('E' . $row, __('Wholesale Price', 'woo-all-in-one-catalogue'))
                    ->getStyle('E'.$row)->applyFromArray(wooaioc_get_row_style('product_table_header'));

        $row++;
        foreach ($item['products'] as $product) {
            $product_data = $product->get_data();

            $spreadsheet->getActiveSheet()->setCellValue('A' . $row, $product_data['sku'])
                        ->getStyle('A'.$row)->applyFromArray(wooaioc_get_row_style('product_table_body'));
            $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $product_data['name'])
                        ->getStyle('B'.$row)->applyFromArray(wooaioc_get_row_style('product_table_body'));
            $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $product_data['description'])
                        ->getStyle('C'.$row)->applyFromArray(wooaioc_get_row_style('product_table_body'));
            $spreadsheet->getActiveSheet()->setCellValue('D' . $row, '')
                        ->getStyle('D'.$row)->applyFromArray(wooaioc_get_row_style('product_table_body'));
            $spreadsheet->getActiveSheet()->setCellValue('E' . $row, '')
                        ->getStyle('E'.$row)->applyFromArray(wooaioc_get_row_style('product_table_body'));

            $spreadsheet->getActiveSheet()->getStyle('A'.$row.':E'.$row)
                        ->getAlignment()->setWrapText(true);

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
