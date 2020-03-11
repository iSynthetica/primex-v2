<?php
/**
 * Product ajax search
 */
function snth_ajax_search_enqueue_scripts() {
     wp_enqueue_style( 'ajax_search-css', SNTH_STYLES_URL.'/ajax-search.css', array(), SNTH_VERSION . time(), 'all' );

     wp_enqueue_script( 'ajax_search-js', SNTH_SCRIPTS_URL.'/ajax-search.js', array( 'jquery' ), SNTH_VERSION . time(), true );

    wp_localize_script( 'site-js', 'snthAjaxObj', array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' )
    ) );
}

// add_action('wp_enqueue_scripts', 'snth_ajax_search_enqueue_scripts', 999);

function snth_ajax_search() {
    $q = !empty($_POST['q']) ? $_POST['q'] : '';
    $message = $q;

    if (!empty($q)) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%{$q}%' AND post_type = 'product'";
        $sql = "";
        $sql .= "SELECT * ";
        $sql .= "FROM {$wpdb->posts} AS p ";
        $sql .= "JOIN {$wpdb->postmeta} AS pm ";
        $sql .= "ON p.ID = pm.post_id ";
        $sql .= "WHERE p.post_type = 'product' ";
        $sql .= "AND (";
        $sql .= "p.post_title LIKE '%".$q."%' ";
        $sql .= "OR(pm.meta_key LIKE '%sku%' AND pm.meta_value LIKE '%".$q."%')";
        $sql .= ") ";
        $sql .= "GROUP BY p.ID;";

        // SELECT *
        // FROM `tsvt_posts` AS p
        // JOIN `tsvt_postmeta` AS pm
        // ON p.ID = pm.post_id
        // WHERE p.post_type = 'product'
        // AND (p.post_title LIKE '%зеркал%' OR(pm.meta_key LIKE '%sku%' AND pm.meta_value LIKE '1404'))


        $result = $wpdb->get_results($sql, ARRAY_A);

        if (!empty($result)) {
            ob_start();
            foreach($result as $item) {
                $product = wc_get_product($item['ID']);
                $sku = $product->get_sku();
                ?>
                <p>
                    <a href="<?php echo get_post_permalink( $item['ID'], false, true )?>" target="_blank">
                        <?php echo $item['post_title'] ?><?php echo !empty($sku) ? ' ('.__( 'SKU:', 'woocommerce' ) .' '.$sku.')' : ''; ?>
                    </a>
                </p>
                <?php
            }
            $search_result = ob_get_clean();
            echo json_encode(array( 'success' => 1, 'error' => 0, 'searchResult' => $search_result ));

            die;
        }
    }

    echo json_encode(array( 'success' => 1, 'error' => 0, 'message' => $message ));

    die;
}

add_action('wp_ajax_nopriv_snth_ajax_search', 'snth_ajax_search');
add_action('wp_ajax_snth_ajax_search', 'snth_ajax_search');