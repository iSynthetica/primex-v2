<h1>Export Products</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';

global $wpdb;

$count_sql = "SELECT COUNT(*) AS count
    FROM {$wpdb->posts} 
    WHERE post_type = 'product';
";

$count = $wpdb->get_row( $count_sql, ARRAY_A );
$limit = 250;
$page = !(empty($_GET['import_page'])) ? sanitize_text_field($_GET['import_page']) : 1;

if (!empty($count)) {
    $pages = ceil((int)$count['count'] / $limit);

    ?>
    <ul>
        <?php
        for ($p = 1; $p <= $pages; $p++) {
            if ((int)$p === (int)$page) {
                ?>
                <li style="display: inline-block; margin-right: 10px;"><?php echo $p; ?></li>
                <?php

            } else {
                ?>
                <li style="display: inline-block; margin-right: 10px;"><a href="?page=wooaioie-page&subpage=export_products&import_page=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                <?php
            }
        }
        ?>
    </ul>
    <?php
}

$offset = $limit * ($page - 1);

echo "<pre>";
var_dump($count);
var_dump($pages);
var_dump($page);
var_dump($limit);
var_dump($offset);
echo "</pre>";

if (empty($offset)) {
    $sql = "SELECT ID,  post_content, post_title, post_excerpt, post_status, post_name, post_parent, post_type
    FROM {$wpdb->posts} 
    WHERE post_type = 'product'
    LIMIT {$limit};
";
} else {
    $sql = "SELECT ID,  post_content, post_title, post_excerpt, post_status, post_name, post_parent, post_type
    FROM {$wpdb->posts} 
    WHERE post_type = 'product'
    LIMIT {$limit} OFFSET {$offset};
";
}

$products = $wpdb->get_results( $sql, ARRAY_A );

$sql = "SELECT tr.object_id, tr.term_taxonomy_id, tr.term_order, tt.taxonomy, t.name, t.slug, tt.parent
FROM {$wpdb->term_relationships} AS tr
JOIN {$wpdb->term_taxonomy} AS tt
ON tr.term_taxonomy_id = tt.term_taxonomy_id
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
WHERE tt.taxonomy NOT IN ('category', 'action-group', 'post_format', 'post_tag', 'nav_menu');";

$terms = $wpdb->get_results( $sql, ARRAY_A );

foreach ($products as $product_i => $product) {
    $product['name'] = $product['post_title'];
    unset($product['post_title']);

    $product['slug'] = $product['post_name'];
    unset($product['post_name']);

    $product['description'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($product['post_content']))));
    unset($product['post_content']);

    $product['short_description'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($product['post_excerpt']))));
    unset($product['post_excerpt']);

    $product['status'] = $product['post_status'];
    unset($product['post_status']);

    $product_terms = array();

    foreach ($terms as $term_i => $term_value) {
        if ($term_value['object_id'] === $product['ID']) {
            if ($term_value['taxonomy'] === 'product_type') {
                $product['product_type'] = $term_value['name'];
            } elseif ($term_value['taxonomy'] === 'product_cat') {
                $product['categories'][] = $term_value;
            } elseif (strpos ($term_value['taxonomy'], 'pa_') === 0) {
                $product['attributes'][] = $term_value;
            } else {
                $product_terms[] = $term_value;
            }

            unset($terms[$term_i]);
        }
    }

    $meta = wooaioie_get_product_meta($product['ID']);

    foreach ($meta as $meta_i => $meta_value) {
        if ($meta_value['meta_key'] === '_sku') {
            $product['sku'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ( !in_array( $product['product_type'], array( 'variable', 'grouped' ) ) ) {
            if ($meta_value['meta_key'] === '_regular_price') {
                $product['regular_price'] = $meta_value['meta_value'];
                unset($meta[$meta_i]);
            }

            if ($meta_value['meta_key'] === '_sale_price') {
                $product['sale_price'] = $meta_value['meta_value'];
                unset($meta[$meta_i]);
            }

            if ($meta_value['meta_key'] === '_price') {
                $product['price'] = $meta_value['meta_value'];
                unset($meta[$meta_i]);
            }
        } else {
            $product['price'] = '';
            $product['regular_price'] = '';
            $product['sale_price'] = '';
        }

        if ($meta_value['meta_key'] === '_sale_price_dates_from') {
            $product['date_on_sale_from'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_custom_manual') {
            $product['_custom_manual'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_retail_percent') {
            $product['_retail_percent'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === 'video_group') {
            $product['video_group'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_yoast_wpseo_primary_product_cat') {
            $product['_yoast_wpseo_primary_product_cat'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_sale_price_dates_to') {
            $product['date_on_sale_to'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_virtual') {
            $product['virtual'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_product_attributes') {
            $product['attributes_meta'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_downloadable') {
            $product['downloadable'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_download_limit') {
            $product['download_limit'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_download_expiry') {
            $product['download_expiry'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_downloadable_files') {
            $product['downloads'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_backorders') {
            $product['backorders'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_sold_individually') {
            // $product['sold_individually'] = $meta_value['meta_value'] === 'no' ? 0 : 1;
            $product['sold_individually'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_manage_stock') {
            $product['manage_stock'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_tax_status') {
            $product['tax_status'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_tax_class') {
            $product['tax_class'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === 'total_sales') {
            $product['total_sales'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_stock_status') {
            $product['stock_status'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_length') {
            $product['weight'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_width') {
            $product['width'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_height') {
            $product['height'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_weight') {
            $product['length'] = $meta_value['meta_value'];

            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_thumbnail_id') {
            $product['image_url'] = $meta_value['meta_value'];
            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_product_image_gallery') {
            $product['gallery_image_url'] = $meta_value['meta_value'];
            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_upsell_ids') {
            $product['upsell_ids'] = $meta_value['meta_value'];
            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_crosssell_ids') {
            $product['cross_sell_ids'] = $meta_value['meta_value'];
            unset($meta[$meta_i]);
        }

        if ($meta_value['meta_key'] === '_purchase_note') {
            $product['purchase_note'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($meta_value['meta_value']))));
            unset($meta[$meta_i]);
        }

        if (
                $meta_value['meta_key'] === '_product_version' ||
                $meta_value['meta_key'] === '_wpcom_is_markdown' ||
                $meta_value['meta_key'] === '_wp_old_slug' ||
                $meta_value['meta_key'] === '_edit_lock' ||
                $meta_value['meta_key'] === '_edit_last') {
            unset($meta[$meta_i]);
        }
    }


    $product['meta'] = $meta;
    $product['terms'] = $product_terms;

    $sql = "SELECT ID,  post_content, post_title, post_excerpt, post_status, post_name, post_parent, post_type
    FROM {$wpdb->posts} 
    WHERE post_type = 'product_variation' AND post_parent = {$product['ID']}";
    $variations = $wpdb->get_results( $sql, ARRAY_A );

    foreach ($variations as $variation_i => $variation) {
        $variation['post_content'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($variation['post_content']))));
        $variation['post_excerpt'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($variation['post_excerpt']))));
        $variation['meta'] = wooaioie_get_product_meta($variation['ID']);

        $variation['terms'] = array();

        foreach ($terms as $term_i => $term_value) {
            if ($term_value['object_id'] === $variation['ID']) {
                $variation['terms'][] = $term_value;
                unset($terms[$term_i]);
            }
        }

        $variations[$variation_i] = $variation;
    }

    $product['variations'] = $variations;

    $products[$product_i] = $product;
}

$products_ser = serialize($products);

?>
<textarea id="" rows="10" style="width: 100%;max-width: 1000px;"><?php echo $products_ser ?></textarea>
<?php

echo "<pre>";

//foreach ($products as $product) {
//    if (!in_array($product['product_type'], array('variable', 'grouped', 'external'))) {
//        print_r($product);
//    }
//}

echo "</pre>";