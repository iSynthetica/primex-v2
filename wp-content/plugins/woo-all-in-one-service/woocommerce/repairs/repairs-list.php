<?php
/**
 *
 */
?>

<div id="wooaioservice_messages_container"></div>

<?php include (WOO_ALL_IN_ONE_SERVICE_PATH . 'woocommerce/repairs/repairs-form.php');?>

<div id="wooaioservice_list_container">
    <?php
    if (empty($repairs)) {
        ?>

        <?php
    } else {
        ?>
        <table id="wooaioservice-table" class="wooaioservice-table shop_table table table-hover table-sm shop_table_responsive">
            <?php
            foreach ($repairs as $repair) {
                ?>
                <tr>
                    <td>
                        <?php echo $repair['title']; ?>
                    </td>

                    <td>
                        <?php echo $repair['product']; ?>
                    </td>

                    <td>
                        <?php echo $repair['fault']; ?>
                    </td>

                    <td>
                        <?php
                        echo "<pre>";
                        print_r($repair);
                        echo "</pre>";
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
    ?>
</div>
