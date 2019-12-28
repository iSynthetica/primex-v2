<h1>Tables</h1>

<style>
    .table-columns {
        width: 100%;
    }
    .table-columns th,
    .table-columns td {
        padding: 6px 15px;
        background-color: #ccc;
    }
</style>

<?php
global $wpdb;
include WOOAIOIE_PATH . 'admin/partials/menu.php';

if (empty($_GET['table'])) {
    $sql = "SHOW TABLES";
    $tables = $wpdb->get_results( $sql, ARRAY_N );

    //var_dump($tables);

    if (!empty($tables)) {
        ?>
        <h2>Tables</h2>

        <ul>
            <?php
            foreach ($tables as $table) {
                $table_name = wooaioie_clean_table_title($table[0]);

                if (!in_array($table_name, wooaioie_get_useless_table_titles())) {
                    $show = false;
                    $title_array = explode('_', $table_name);
                    $title_array_count = count($title_array);

                    if (1 === $title_array_count) {
                        $show = true;
                    } elseif (in_array($title_array[0], array('woocommerce', 'wc', 'term'))) {
                        $show = true;
                    }

                    if ($show) {
                        ?>
                        <li><a href="?page=wooaioie-page&subpage=tables&table=<?php echo $table_name ?>"><?php echo $table_name ?></a></li>
                        <?php
                    }
                }
            }
            ?>
        </ul>
        <?php
    }
} else {
    $table_name = sanitize_text_field($_GET['table']);
    $table_db_name = $wpdb->prefix . $table_name;
    $sql = "DESCRIBE {$table_db_name}";
    $tables = $wpdb->get_results( $sql, ARRAY_A );
    if (!empty($tables)) {
        ?>
        <h2>Table "<?php echo $table_name ?>"</h2>
        <div style="max-width: 1000px;width: 100%;">
            <table class="table-columns">
                <thead>
                    <tr>
                        <?php
                        foreach ($tables[0] as $param_title => $column) {
                            ?>
                            <th><?php echo $param_title ?></th>
                            <?php
                        }
                        ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    foreach ($tables as $column) {
                        ?>
                        <tr>
                            <?php
                            foreach ($column as $param) {
                                ?>
                                <td><?php echo $param ?></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                        //var_dump($column);
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

}