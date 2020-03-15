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

function wooaioc_get_categories_tree($parent = 0) {
    $tree = array();

    $next = get_terms(array(
        'taxonomy' => 'product_cat',
        'parent' => $parent
    ));

    if (!empty($next)) {
        foreach ($next as $cat) {
            $cat_stored = array(
                'id' => $cat->term_id,
                'name' => $cat->name,
            );

            $tree[$cat->term_id] = array(
                'category' => $cat_stored,
                'children' => wooaioc_get_categories_tree($cat->term_id)
            );
        }
    }

    return $tree;
}

function wooaioc_display_xml_category_item($category, $parent_id = 0) {
    if (empty($parent_id)) {
        echo "\t\t\t\t".'<category id="'.$category['category']['id'].'">'.$category['category']['name'].'</category>'.PHP_EOL;
    } else {
        echo "\t\t\t\t".'<category id="'.$category['category']['id'].'" parentId="'.$parent_id.'">'.$category['category']['name'].'</category>'.PHP_EOL;
    }

    if (!empty($category['children'])) {
        foreach ($category['children'] as $cat_children) {
            wooaioc_display_xml_category_item($cat_children, $category['category']['id']);
        }
    }
}

function wooaioc_get_products() {
    $args = array(
        'limit'    => -1,
        'status'    => 'publish',
    );

    $_products = wc_get_products( $args );
    $products = array();

    foreach ($_products as $_product) {
        $product_type = $_product->get_type();

        if ('grouped' === $product_type || 'external' === $product_type) {
            continue;
        }

        if ('variable' === $product_type) {
            $available_variations = $_product->get_available_variations();
            $parent_product_id = $_product->get_id();

            if (!empty($available_variations)) {
                foreach ($available_variations as $variation) {
                    $variation_product = wc_get_product($variation['variation_id']);

                    $product_id = $variation_product->get_id();

                    $products[$product_id] = array(
                        'id' => $product_id,
                        'price' => $variation_product->get_price(),
                        'name' => $variation_product->get_name(),
                        'type' => 'variation',
                        'vendor' => 'Prime-X',
                        'url' => get_permalink($parent_product_id),
                    );

                    $product_sku = $variation_product->get_sku();

                    if (!empty($product_sku)) {
                        $products[$product_id]['sku'] = $product_sku;
                    }

                    $product_images = wooaioc_get_images( $variation_product );

                    if (!empty($product_images)) {
                        $products[$product_id]['images'] = $product_images;
                    }

                    $product_attributes = wooaioc_get_attributes( $variation_product );

                    if (!empty($product_attributes)) {
                        $products[$product_id]['attributes'] = $product_attributes;
                    }
                }
            }
        } else {
            $product_id = $_product->get_id();

            $products[$product_id] = array(
                'id' => $product_id,
                'price' => $_product->get_price(),
                'name' => $_product->get_name(),
                'type' => $_product->get_type(),
                'vendor' => 'Prime-X',
                'url' => get_permalink($product_id),
            );

            $product_sku = $_product->get_sku();

            if (!empty($product_sku)) {
                $products[$product_id]['sku'] = $product_sku;
            }

            $product_images = wooaioc_get_images( $_product );

            if (!empty($product_images)) {
                $products[$product_id]['images'] = $product_images;
            }

            $product_attributes = wooaioc_get_attributes( $_product );

            if (!empty($product_attributes)) {
                $products[$product_id]['attributes'] = $product_attributes;
            }
        }

    }

    return $products;
}

function wooaioc_get_images( $product ) {
    $images        = $attachment_ids = array();
    $product_image = $product->get_image_id();

    // Add featured image.
    if ( ! empty( $product_image ) ) {
        $attachment_ids[] = $product_image;
    }

    // Add gallery images.
    $attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

    // Build image data.
    foreach ( $attachment_ids as $position => $attachment_id ) {

        $attachment_post = get_post( $attachment_id );

        if ( is_null( $attachment_post ) ) {
            continue;
        }

        $attachment = wp_get_attachment_image_src( $attachment_id, 'full' );

        if ( ! is_array( $attachment ) ) {
            continue;
        }

        $images[] = array(
            'id'         => (int) $attachment_id,
            'src'        => current( $attachment ),
            'title'      => get_the_title( $attachment_id ),
        );
    }

    // Set a placeholder image if the product has no images set.
    if ( empty( $images ) ) {

        $images[] = array(
            'id'         => 0,
            'src'        => wc_placeholder_img_src(),
            'title'      => __( 'Placeholder', 'woocommerce' ),
        );
    }

    return $images;
}

function wooaioc_get_attributes( $product ) {

    $attributes = array();

    if ( $product->is_type( 'variation' ) ) {
        // variation attributes
        foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {

            // taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`
            $attributes[] = array(
                'name'   => wc_attribute_label( str_replace( 'attribute_', '', $attribute_name ) ),
                'slug'   => str_replace( 'attribute_', '', wc_attribute_taxonomy_slug( $attribute_name ) ),
                'option' => $attribute,
            );
        }
    } else {
        foreach ( $product->get_attributes() as $attribute ) {
            $attributes[] = array(
                'name'      => wc_attribute_label( $attribute['name'] ),
                'slug'      => wc_attribute_taxonomy_slug( $attribute['name'] ),
                'position'  => (int) $attribute['position'],
                'visible'   => (bool) $attribute['is_visible'],
                'variation' => (bool) $attribute['is_variation'],
                'options'   => wooaioc_get_attribute_options( $product->get_id(), $attribute ),
            );
        }
    }

    return $attributes;
}

function wooaioc_get_attribute_options( $product_id, $attribute ) {
    if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
        return wc_get_product_terms( $product_id, $attribute['name'], array( 'fields' => 'names' ) );
    } elseif ( isset( $attribute['value'] ) ) {
        return array_map( 'trim', explode( '|', $attribute['value'] ) );
    }

    return array();
}

function wooaioc_display_xml_product_item($product) {
    echo "\t\t\t\t".'<offer id="'.$product['id'].'" available="true">'.PHP_EOL;
    echo "\t\t\t\t\t".'<url>'.$product['url'].'</url>'.PHP_EOL;
    echo "\t\t\t\t\t".'<price>'.$product['price'].'</price>'.PHP_EOL;
    echo "\t\t\t\t\t".'<stock_quantity>100</stock_quantity>'.PHP_EOL;

    if (!empty($product['images'])) {
        foreach ($product['images'] as $image) {
            if (!empty($image['src'])) {
                echo "\t\t\t\t\t".'<picture>'.$image['src'].'</picture>'.PHP_EOL;
            }
        }
    }

    echo "\t\t\t\t\t".'<currencyId>UAH</currencyId>'.PHP_EOL;
    echo "\t\t\t\t\t".'<name>'.sanitize_text_field($product['name']).'</name>'.PHP_EOL;
    echo "\t\t\t\t\t".'<type>'.$product['type'].'</type>'.PHP_EOL;

    if (!empty($product['sku'])) {
        echo "\t\t\t\t\t".'<param name="Артикул">'.$product['sku'].'</param>'.PHP_EOL;
    }

    if (!empty($product['attributes'])) {
        foreach ($product['attributes'] as $attribute) {
            if (!empty($attribute['name']) && !empty($attribute['options'])) {
                foreach ($attribute['options'] as $option) {
                    echo "\t\t\t\t\t".'<param name="'.$attribute['name'].'">'.$option.'</param>'.PHP_EOL;
                }
            }
        }
    }
    echo "\t\t\t\t\t".'<vendor>Abc clothes</vendor>'.PHP_EOL;

    echo "\t\t\t\t".'</offer>'.PHP_EOL;
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
                'status'    => 'publish',
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
                    $product_type = $product->get_type();

                    if ('grouped' === $product_type) {
                        continue;
                    }

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
                    } else {
                        $product->catalogue_price_html = $product->get_price_html();

                        $stored_product = array(
                            'id' => $product->get_id(),
                            'catalogue_price_html' => $product->get_price_html(),
                        );

                        $tree[$cat->term_id]['products'][$product->get_id()] = $stored_product;
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
                    <?php
                    if (false) {
                        ?>
                        <td class="product-short-description responsive-hide">
                            <?php do_action('wooaioc_display_catalogue_item_description', $product); ?>
                        </td>
                        <?php
                    }
                    ?>
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
    if (function_exists('run_woo_all_in_one_discount')) {
        global $wooaiodiscount_current_user_rule;

        $fields = array(
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
        );

        if (!empty($wooaiodiscount_current_user_rule["base_discount"]["discount_label"])) {
            $fields['D']['title'] = $wooaiodiscount_current_user_rule["base_discount"]["discount_label"] . ' ' . html_entity_decode(get_woocommerce_currency_symbol());
        }

        if (
            $wooaiodiscount_current_user_rule["before_discount"]["show_before_discount"] === 'yes' &&
            !empty($wooaiodiscount_current_user_rule["before_discount"]["discount"]) &&
            !empty($wooaiodiscount_current_user_rule["before_discount"]["discount_label"])
        ) {
            $fields['E'] = array(
                'width' => '16',
                'title' => $wooaiodiscount_current_user_rule["before_discount"]["discount_label"] . ' ' . html_entity_decode(get_woocommerce_currency_symbol()),
                'field' => 'before_discount_price',
                'format' => 'money'
            );
        }

        return $fields;
    } else {
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
        );
    }
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

function wooaioc_get_row_product_item($product_stored, $spreadsheet, $row) {
    $product = wc_get_product($product_stored['id']);
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
            return sanitize_textarea_field($product_data['description']);
        case 'price':
            $price = $product->get_price();
            return $price;
        case 'before_discount_price':
            if (function_exists('run_woo_all_in_one_discount')) {
                global $wooaiodiscount_current_user_rule;
                wooaiodiscount_reset_discount_rules();
                wooaiodiscount_set_before_discount_rules();
                $price = $product->get_price();
                wooaiodiscount_reset_before_discount_rules();
                wooaiodiscount_set_discount_rules();
                return $price;
            } else {
                return '';
            }
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


    add_rewrite_rule(
        '^wooaioc-api/(.*)/([^/]*)/?',
        'index.php?api_catalogue=1&api_version=$matches[1]&api_format=$matches[2]',
        'top'
    );

    add_rewrite_tag('%api_catalogue%', '(.*)');
    add_rewrite_tag('%api_version%', '(.*)');
    add_rewrite_tag('%api_format%', '(.*)');
}

function wooaioc_download_catalogues($item, $depth = 0) {
    $file_format = get_query_var('file_format');

    if ($file_format) {
        include WOOAIOCATALOGUE_PATH . '/parts/download.php';

        die;
    }
}
add_action('template_redirect', 'wooaioc_download_catalogues');

function wooaioc_api() {
    $api_catalogue = get_query_var('api_catalogue');
    $api_version = get_query_var('api_version');

    if (!empty($api_catalogue) && !empty($api_version)) {
        $api = 'api_v' . $api_version;

        if (file_exists(WOOAIOCATALOGUE_PATH . '/includes/'.$api.'.php')) {
            include_once WOOAIOCATALOGUE_PATH . '/includes/'.$api.'.php';

            $content = wooaioc_api_get_content();
        } else {
            include_once WOOAIOCATALOGUE_PATH . '/includes/no-api.php';

            $content = wooaioc_api_get_content();
        }

        include WOOAIOCATALOGUE_PATH . '/parts/import.php';

        die;
    }
}
add_action('template_redirect', 'wooaioc_api');

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
