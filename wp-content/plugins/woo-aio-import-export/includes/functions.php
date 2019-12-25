<?php
function wooaioie_get_fields() {
    return array(
        'id' => 'ID',
        'type' => 'Type',
        'sku' => 'SKU',
        'name' => 'Name',
        'published' => 'Published',
        'featured' => '"Is featured?"',
        'catalog_visibility' => '"Visibility in catalog"',
        'short_description' => '"Short description"',
        'description' => 'Description',
        'date_on_sale_from' => '"Date sale price starts"',
        'date_on_sale_to' => '"Date sale price ends"',
        'tax_status' => '"Tax status"',
        'tax_class' => '"Tax class"',
        'stock_status' => '"In stock?"',
        'stock' => 'Stock',
        'backorders_allowed' => '"Backorders allowed?"',
        'sold_individually' => '"Sold individually?"',
        'weight_lbs' => '"Weight (lbs)"',
        'length_in' => '"Length (in)"',
        'width_in' => '"Width (in)"',
        'height_in' => '"Height (in)"',
        'allow_customer_reviews' => '"Allow customer reviews?"',
        'purchase_note' => '"Purchase note"',
        'sale_price' => '"Sale price"',
        'regular_price' => '"Regular price"',
        'categories' => 'Categories',
        'tags' => 'Tags',
        'shipping_class' => '"Shipping class"',
        'images' => 'Images',
        'download_limit' => '"Download limit"',
        'download_expiry_days' => '"Download expiry days"',
        'parent' => 'Parent',
        'grouped_products' => '"Grouped products"',
        'upsells' => 'Upsells',
        'cross-sells' => 'Cross-sells',
        'external_url' => '"External URL"',
        'button_text' => '"Button text"',
        'position' => 'Position',
        'attribute_1_name' => '"Attribute 1 name"',
        'attribute_1_values' => '"Attribute 1 value(s)"',
        'attribute_1_visible' => '"Attribute 1 visible"',
        'attribute_1_global' => '"Attribute 1 global"',
        'attribute_2_name' => '"Attribute 2 name"',
        'attribute_2_values' => '"Attribute 2 value(s)"',
        'attribute_2_visible' => '"Attribute 2 visible"',
        'attribute_2_global' => '"Attribute 2 global"',
        'meta_wpcom_is_markdown' => '"Meta: _wpcom_is_markdown"',
        'download_1_name' => '"Download 1 name"',
        'download_1_url' => '"Download 1 URL"',
        'download_2_name' => '"Download 2 name"',
        'download_2_url' => '"Download 2 URL"',
    );
}

function wooaioie_get_useless_table_titles() {
    return array(
        'commentmeta',
        'comments',
        'links',
        'usermeta',
        'users',
        'layerslider',
    );
}


function wooaioie_get_product_meta($id) {
    global $wpdb;

    $sql = "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = '{$id}'";
    $meta = $wpdb->get_results( $sql, ARRAY_A );

    foreach ($meta as $meta_i => $meta_item) {
        if ($meta_item['meta_key'] === '_thumbnail_id') {
            $thumbnail_id = $meta_item['meta_value'];
            $image_src = wp_get_attachment_image_src($thumbnail_id, 'full');

            if (!empty($image_src)) {
                $meta[$meta_i]['meta_value'] = $image_src[0];
            }
        } elseif ($meta_item['meta_key'] === '_variation_description') {
            $meta[$meta_i]['meta_value'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($meta_item['meta_value']))));
        } elseif ($meta_item['meta_key'] === '_product_image_gallery') {
            $image_gallery_ids = explode(',', $meta_item['meta_value']);
            $image_gallery_urls = array();


            foreach ($image_gallery_ids as $image_gallery_id) {
                $image_gallery_src = wp_get_attachment_image_src($image_gallery_id, 'full');
                if (!empty($image_gallery_src)) {
                    $image_gallery_urls[] = $image_gallery_src[0];
                }
            }

            $meta[$meta_i]['meta_value'] = $image_gallery_urls;
        } elseif (false !== strpos($meta_item['meta_key'], '_oembed_')) {
            unset($meta[$meta_i]);
        }
    }

    return $meta;
}

function wooaioie_clean_table_title($title) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $prefix_length = strlen($prefix);

    return substr($title, $prefix_length);
}

function wooaioie_add_term($term_data, $parent_id = 0, $import_id = 'product_cat_ID', $taxonomy = 'product_cat') {
    global $wpdb;
    $sql = "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = '_import_{$import_id}' AND meta_value = '{$term_data['term_taxonomy_id']}'";
    $term_exists = $wpdb->get_row( $sql, ARRAY_A );

    if ($term_exists) {
        $created_id = $term_exists['term_id'];
    } else {
        $title = $term_data['name'];
        $args = array(
            'description' => htmlspecialchars_decode (base64_decode($term_data['description'])),
            'slug'        => $term_data['slug'],
            'parent'      => $parent_id
        );

        $insert_data = wp_insert_term($title, $taxonomy, $args);
        $created_id = $insert_data['term_id'];
        add_term_meta( $created_id, '_import_' . $import_id, $term_data['term_taxonomy_id'], true );
    }

    return $created_id;
}

function wooaioie_add_term_meta($term_id, $term_metas) {
    global $wpdb;

    foreach ($term_metas as $term_meta) {
        $sql = "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = '{$term_meta['meta_key']}' AND term_id = '{$term_id}'";
        $term_thumbnail = $wpdb->get_row( $sql, ARRAY_A );

        if (!empty($term_thumbnail)) {
            continue;
        }

        if ($term_meta['meta_key'] === 'thumbnail_id') {
            if (empty($term_thumbnail)) {
                $url = $term_meta['meta_value'][0];
                $upload = wc_rest_upload_image_from_url( $url );

                if ( !is_wp_error( $upload ) ) {
                    $upload_id = wc_rest_set_uploaded_image_as_attachment( $upload, 0 );

                    if ( wp_attachment_is_image( $upload_id ) ) {
                        add_term_meta( $term_id, $term_meta['meta_key'], $upload_id );
                    }
                }
            }
        } elseif ($term_meta['meta_key'] === 'product_count_product_cat') {

        } else {
            add_term_meta( $term_id, $term_meta['meta_key'], $term_meta['meta_value'], true );
        }
    }
}

function wooaioie_create_single_product($product_value) {
    error_log('TASK STARTED ========================================= ');
    global $wpdb;

    $skus_sql = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_sku'";
    $skus = $wpdb->get_results( $skus_sql, ARRAY_A );
    $skus_array = array();

    foreach ($skus as $sku_value) {
        $skus_array[] = $sku_value['meta_value'];
    }

    if ($product_value['product_type'] === 'simple') {
        $sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_import_product_ID' AND meta_value = '{$product_value['ID']}'";
        $product_exists = $wpdb->get_row( $sql, ARRAY_A );

        if ($product_exists) {
            $created_id = $product_exists['post_id'];
        } else {
            $objProduct = new WC_Product_Simple();
            $created_id = $objProduct->save();
        }

        update_post_meta($created_id, '_import_product_ID', $product_value['ID'] );

        if ($created_id) {
            $simple_product = wc_get_product( $created_id );
            $simple_product->set_name($product_value['name']);
            $simple_product->set_slug($product_value['slug']);
            $simple_product->set_status($product_value['status']);

            $description = htmlspecialchars_decode (base64_decode($product_value['description']));
            $array = array();
            preg_match_all( '/src="([^"]*)"/i', $description, $array ) ;
            // print_r( $array[1] ) ;

            $simple_product->set_description($description);

            $simple_product->set_short_description(htmlspecialchars_decode (base64_decode($product_value['short_description'])));

            unset($product_value['name']);
            unset($product_value['slug']);
            unset($product_value['post_type']);
            unset($product_value['product_type']);
            unset($product_value['status']);
            unset($product_value['description']);
            unset($product_value['short_description']);

            if (!empty($product_value['sku'])) {
                $sku = $product_value['sku'];
                if (in_array($sku, $skus_array)) {
                    $sku = $sku . '-' . time();
                }
                $simple_product->set_sku($sku);
                unset($product_value['sku']);
            }

            if (!empty($product_value['price'])) {
                $simple_product->set_price($product_value['price']);
                unset($product_value['price']);
            }

            if (!empty($product_value['regular_price'])) {
                $simple_product->set_regular_price($product_value['regular_price']);
                unset($product_value['regular_price']);
            }

            if (!empty($product_value['sale_price'])) {
                $simple_product->set_regular_price($product_value['sale_price']);
                unset($product_value['sale_price']);
            }

            if (!empty($product_value['manage_stock'])) {
                $simple_product->set_manage_stock($product_value['manage_stock']);
                unset($product_value['manage_stock']);
            }

            if (!empty($product_value['total_sales'])) {
                $simple_product->set_total_sales($product_value['total_sales']);
                unset($product_value['total_sales']);
            }

            if (!empty($product_value['attributes_meta'])) {
                if (!empty(unserialize($product_value['attributes_meta']))) {
                    $sql = "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = '{$created_id}' AND meta_key = '_product_attributes'";
                    $meta_exists = $wpdb->get_row( $sql, ARRAY_A );

                    if (empty($meta_exists)) {
                        $wpdb->insert(
                            $wpdb->postmeta,
                            array( 'post_id' => $created_id, 'meta_key' => '_product_attributes', 'meta_value' => $product_value['attributes_meta'] ),
                            array( '%d', '%s', '%s' )
                        );
                    }
                }
                unset($product_value['attributes_meta']);
            }

            if (!empty($product_value['attributes'])) {
                foreach ($product_value['attributes'] as $attribute) {
                    $sql = "SELECT t.term_id,  tt.term_taxonomy_id, tt.taxonomy, t.slug
                            FROM {$wpdb->terms} AS t
                            JOIN {$wpdb->term_taxonomy} AS tt
                            ON t.term_id = tt.term_taxonomy_id
                            WHERE tt.taxonomy = '{$attribute['taxonomy']}'
                            AND t.slug = '{$attribute['slug']}'
                            ";

                    $term_exists = $wpdb->get_row( $sql, ARRAY_A );

                    if (!empty($term_exists)) {
                        $sql = "SELECT object_id FROM {$wpdb->term_relationships} WHERE object_id = '{$created_id}' AND term_taxonomy_id = '{$term_exists['term_taxonomy_id']}'";
                        $term_assigned = $wpdb->get_row( $sql, ARRAY_A );

                        if (empty($term_assigned)) {
                            $wpdb->insert(
                                $wpdb->term_relationships,
                                array( 'object_id' => $created_id, 'term_taxonomy_id' => $term_exists['term_taxonomy_id'], 'term_order' => $attribute['term_order'] ),
                                array( '%d', '%d', '%d' )
                            );
                        }
                    }
                }
                unset($product_value['attributes']);
            }

            if (!empty($product_value['image_url'])) {
                if (!$simple_product->get_image_id()) {
                    $upload = wc_rest_upload_image_from_url( $product_value['image_url'] );

                    if ( !is_wp_error( $upload ) ) {
                        $upload_id = wc_rest_set_uploaded_image_as_attachment( $upload, $created_id );

                        if ( wp_attachment_is_image( $upload_id ) ) {
                            $simple_product->set_image_id($upload_id);
                        }
                    }
                }
                unset($product_value['image_url']);
            }

            if (!empty($product_value['gallery_image_url'])) {
                if (!$simple_product->get_gallery_image_ids()) {
                    $image_ids = array();

                    foreach ($product_value['gallery_image_url'] as $image_url) {
                        $upload = wc_rest_upload_image_from_url( $image_url );

                        if ( !is_wp_error( $upload ) ) {
                            $upload_id = wc_rest_set_uploaded_image_as_attachment( $upload, 0 );

                            $image_ids[] = $upload_id;
                        }
                    }

                    if (!empty($image_ids)) {
                        $simple_product->set_gallery_image_ids($image_ids);
                    }
                }
                unset($product_value['gallery_image_url']);
            }

            if (!empty($product_value['categories'])) {
                $categories_array = array();
                foreach ($product_value['categories'] as $category) {
                    $term_taxonomy_id = $category['term_taxonomy_id'];

                    $sql = "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = '_import_product_cat_ID' AND meta_value = '{$category['term_taxonomy_id']}'";
                    $term_exists = $wpdb->get_row( $sql, ARRAY_A );

                    if ($term_exists) {
                        $categories_array[] = $term_exists['term_id'];
                    }
                }

                if (!empty($categories_array)) {
                    $simple_product->set_category_ids($categories_array);
                }
                unset($product_value['categories']);
            }

            $simple_product_id = $simple_product->save();
        }

        ob_start();
        print_r($product_value);
        error_log(ob_get_clean());

        error_log('TASK FINISHED ========================================= ');
    }
}