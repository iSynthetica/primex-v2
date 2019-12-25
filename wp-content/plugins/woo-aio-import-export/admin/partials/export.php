<h1><?php echo __('Export', 'woo-all-in-one-ie'); ?></h1>
<?php
global $wpdb;

$sql = "SELECT tr.object_id, tr.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.parent
FROM {$wpdb->term_relationships} AS tr
JOIN {$wpdb->term_taxonomy} AS tt
ON tr.term_taxonomy_id = tt.term_taxonomy_id
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
WHERE tt.taxonomy NOT IN ('category', 'action-group', 'post_format', 'post_tag', 'nav_menu')
GROUP BY tt.taxonomy;";

$sql = "SELECT tr.object_id, tr.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.parent
FROM {$wpdb->term_relationships} AS tr
JOIN {$wpdb->term_taxonomy} AS tt
ON tr.term_taxonomy_id = tt.term_taxonomy_id
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
WHERE tt.taxonomy NOT IN ('category', 'action-group', 'post_format', 'post_tag', 'nav_menu');";

$terms = $wpdb->get_results( $sql, ARRAY_A );

echo "<pre>";
//print_r(count($terms));
//print_r($terms);
echo "</pre>";

$limit = 50;
$sql = "SELECT ID,  post_content, post_title, post_excerpt, post_status, post_name, post_parent, post_type
FROM {$wpdb->posts} 
WHERE post_type = 'product'
LIMIT {$limit};
";

//$sql = "SELECT ID,  post_title, post_status, post_name, post_parent, post_type
//FROM {$wpdb->posts}
//WHERE post_type = 'product'
//LIMIT {$limit};
//";
//
//$sql = "SELECT post_content
//FROM {$wpdb->posts}
//WHERE post_type = 'product'
//LIMIT {$limit};
//";

$products = $wpdb->get_results( $sql, ARRAY_A );
$products_to_import = array();
echo "<pre>";

foreach ($products as $i => $product) {
    $products[$i]['post_content'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($product['post_content']))));
    $products[$i]['post_excerpt'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($product['post_excerpt']))));
    $products_terms = array();

    foreach ($terms as $in => $term) {
        if ($product['ID'] == $term['object_id']) {
            $products_terms[] = $term;

            unset ($terms[$in]);
        }
    }
    $products[$i]['products_terms'] = $products_terms;
    // $products[$i]['post_content'] = preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($product['post_content'])));
}
// print_r($products);
// $products_converted = serialize($products);
// $products_converted = str_replace('\r\n', '', $products_converted);
print_r($products);
// print_r($products_converted);
// print_r($products);
echo "</pre>";

foreach ($products as $i => $product) {
    $sql = "SELECT meta_key,  meta_value
    FROM {$wpdb->postmeta} 
    WHERE post_id = '{$product['ID']}';
    ";

    $product_meta = $wpdb->get_results( $sql, ARRAY_A );
    $products[$i]['meta'] = $product_meta;

    $products_to_import[] = array(
        'id' => $product['ID'],
        'type' => $product['ID'],
    );
}