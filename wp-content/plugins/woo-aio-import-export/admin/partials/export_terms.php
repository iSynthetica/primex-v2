<h1>Export Terms</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';

global $wpdb;

if (false) {
    if (empty($_GET['taxonomy'])) {
        $sql = "SELECT COUNT(*) AS count, tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.description, tt.parent
FROM  {$wpdb->term_taxonomy} AS tt
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
GROUP BY tt.taxonomy;";

        $terms = $wpdb->get_results( $sql, ARRAY_A );

        if (!empty($terms)) {
            ?>
            <ul>
                <?php
                foreach ($terms as $term) {
                    $term_taxonomy = $term['taxonomy'];

                    if (!in_array($term_taxonomy, array('category', 'nav_menu'))) {
                        ?>
                        <li>
                            <a href="?page=wooaioie-page&subpage=export_terms&taxonomy=<?php echo $term_taxonomy ?>"><?php echo $term_taxonomy ?></a>
                            -
                            (<?php echo $term['count'] ?>)
                            <?php
                            ?>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>
            <?php
        }

    } else {
        $sql = "SELECT tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.description, tt.parent
FROM  {$wpdb->term_taxonomy} AS tt
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
WHERE tt.taxonomy IN ('product_cat')
GROUP BY t.slug;";

        $terms = $wpdb->get_results( $sql, ARRAY_A );

        foreach ($terms as $i => $term) {
            $terms[$i]['description'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($term['description']))));
        }
        echo "<pre>";
        print_r($terms);
        echo "</pre>";
    }
}


$sql = "SELECT tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.description, tt.parent
FROM  {$wpdb->term_taxonomy} AS tt
JOIN {$wpdb->terms} AS t
ON t.term_id = tt.term_id
WHERE tt.taxonomy IN ('product_cat')
GROUP BY t.slug;";

$terms = $wpdb->get_results( $sql, ARRAY_A );

foreach ($terms as $i => $term) {
    $terms[$i]['description'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($term['description']))));
}

foreach ($terms as $ti => $term) {
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

?>
<textarea id="" rows="10" style="width: 100%;max-width: 1000px;"><?php echo $terms_ser; ?></textarea>
<?php

