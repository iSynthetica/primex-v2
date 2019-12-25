<h1>Import Products</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';
$ser_products = '';

?>
<textarea id="import-products-field" rows="10" style="width: 100%;max-width: 1000px;"></textarea><br>
<button id="import-products-button" class="button-primary">Import Products</button>
<?php

if (empty($ser_products)) {
    return;
}

echo "<pre>";
$products = unserialize($ser_products);
global $wpdb;

$i = 0;
$max = 9999;

foreach ($products as $product_i => $product_value) {
    $skus_sql = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_sku'";
    $skus = $wpdb->get_results( $skus_sql, ARRAY_A );
    $skus_array = array();

    foreach ($skus as $sku_value) {
        $skus_array[] = $sku_value['meta_value'];
    }

    if ($product_value['product_type'] === 'simple' && $i < $max) {
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

        print_r($product_value);
        // print_r($simple_product);

        $i++;
    }
}
echo "</pre>";


$data = array(
    'date_created'       => null,
    'date_modified'      => null,
    'featured'           => false,
    'catalog_visibility' => 'visible',
    'stock_quantity'     => null,
    'low_stock_amount'   => '',
    'parent_id'          => 0,
    'reviews_allowed'    => true,
    'default_attributes' => array(),
    'menu_order'         => 0,
    'post_password'      => '',
    'tag_ids'            => array(),
    'shipping_class_id'  => 0,
    'rating_counts'      => array(),
    'average_rating'     => 0,
    'review_count'       => 0,
);


$data = array(
    'name'               => '',
    'slug'               => '',
    'date_created'       => null,
    'date_modified'      => null,
    'status'             => false,
    'featured'           => false,
    'catalog_visibility' => 'visible',
    'description'        => '',
    'short_description'  => '',
    'sku'                => '',
    'price'              => '',
    'regular_price'      => '',
    'sale_price'         => '',
    'date_on_sale_from'  => null,
    'date_on_sale_to'    => null,
    'total_sales'        => '0',
    'tax_status'         => 'taxable',
    'tax_class'          => '',
    'manage_stock'       => false,
    'stock_quantity'     => null,
    'stock_status'       => 'instock',
    'backorders'         => 'no',
    'low_stock_amount'   => '',
    'sold_individually'  => false,
    'weight'             => '',
    'length'             => '',
    'width'              => '',
    'height'             => '',
    'upsell_ids'         => array(),
    'cross_sell_ids'     => array(),
    'parent_id'          => 0,
    'reviews_allowed'    => true,
    'purchase_note'      => '',
    'attributes'         => array(),
    'default_attributes' => array(),
    'menu_order'         => 0,
    'post_password'      => '',
    'virtual'            => false,
    'downloadable'       => false,
    'category_ids'       => array(),
    'tag_ids'            => array(),
    'shipping_class_id'  => 0,
    'downloads'          => array(),
    'image_id'           => '',
    'gallery_image_ids'  => array(),
    'download_limit'     => -1,
    'download_expiry'    => -1,
    'rating_counts'      => array(),
    'average_rating'     => 0,
    'review_count'       => 0,
);