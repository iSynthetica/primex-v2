<a href="?page=wooaioie-page&subpage=import">Import</a>
<a href="?page=wooaioie-page&subpage=export">Export</a>
<a href="?page=wooaioie-page&subpage=serialize">Serialize</a>
<a href="?page=wooaioie-page&subpage=terms">Terms</a>

<?php
global $wpdb;
$sql = "SELECT tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.description, tt.parent
FROM  {$wpdb->term_taxonomy} AS tt
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
WHERE tt.taxonomy IN ('product_cat')
GROUP BY t.slug;";
// WHERE tt.taxonomy NOT IN ('category', 'action-group', 'post_format', 'post_tag', 'nav_menu');";

$terms = $wpdb->get_results( $sql, ARRAY_A );

foreach ($terms as $i => $term) {
    $terms[$i]['description'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($term['description']))));
}

foreach ($terms as $ti => $term) {
//    $sql = "SELECT tm.meta_key, tm.meta_value
//    FROM  {$wpdb->termmeta} AS tm
//    WHERE tm.term_id IN ({$term['term_taxonomy_id']})";

    $sql = "SELECT tm.meta_key, tm.meta_value
    FROM  {$wpdb->prefix}woocommerce_termmeta AS tm
    WHERE tm.woocommerce_term_id IN ({$term['term_taxonomy_id']})";

    $term_metas = $wpdb->get_results( $sql, ARRAY_A );
    $metas = array();

    foreach ($term_metas as $meta) {
        if ($meta['meta_key'] == 'thumbnail_id') {
            $meta['meta_value'] = wp_get_attachment_image_src($meta['meta_value'], 'full');
        }
        $metas[] = $meta;
    }

    $terms[$ti]['meta'] = $metas;
}

$terms_ser = serialize($terms);

$sql = "SHOW TABLES";
$sql = "DESCRIBE {$wpdb->prefix}woocommerce_termmeta";
$tables = $wpdb->get_results( $sql, ARRAY_A );

echo "<pre>";
//var_dump($terms);
echo $terms_ser;
// var_dump($tables);
echo "</pre>";