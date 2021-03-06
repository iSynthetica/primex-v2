<?php
$currency_rules = Woo_All_In_One_Currency_Rules::get_all();
$currency_rules_codes = array_keys($currency_rules);
$woocommerce_currencies = Woo_All_In_One_Currency_Rules::get_woocommerce_currencies();
$base_currency = get_option( 'woocommerce_currency' );
//echo "<pre>";
//print_r($currency_rules);
//echo "</pre>";
?>
<h3 class="wp-heading-inline">
    <?php _e('Currencies List', 'woo-all-in-one-currency'); ?>
</h3>

<div id="wooaio-currency-create-action" class="wooaio-currency-create-closed">
    <button id="open-create-currency-rule" class="button button-primary" type="button"><?php _e('Create new currency rule', 'woo-all-in-one-currency'); ?></button>
    <button id="close-create-currency-rule" class="button" type="button"><?php _e('Cancel', 'woo-all-in-one-currency'); ?></button>
</div>

<div id="wooaio-currency-create-container" class="wooaio-currency-create-closed">
    <form id="wooaio-currency-create-form" style="width: 100%;max-width:600px;">
        <div class="wooaio-container-fluid wooaio-currency-item">
            <div class="wooaio-row">
                <div class="wooaio-col-xs-12 wooaio-col-sm-5">
                    <label for="currency_code"><?php _e('Select Currency', 'woo-all-in-one-currency'); ?></label>
                </div>

                <div class="wooaio-col-xs-12 wooaio-col-sm-7">
                    <select name="currency_code" id="currency_code">
                        <option value=""><?php _e('-- select currency --', 'woo-all-in-one-currency'); ?></option>

                        <?php
                        foreach ($woocommerce_currencies as $currency_code => $currency) {
                            if (!in_array($currency_code, $currency_rules_codes)) {
                                ?>
                                <option value="<?php echo $currency_code; ?>"><?php echo $currency['title']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="wooaio-row">
                <div class="wooaio-col-xs-12 wooaio-col-sm-5">

                </div>

                <div class="wooaio-col-xs-12 wooaio-col-sm-7">
                    <button id="add-currency-submit" class="button" type="button"><?php _e('Add', 'woo-all-in-one-discount'); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="wooaio-discount-list" class="wooaio-list">
    <?php
    if (empty($currency_rules)) {
        ?>

        <?php
    } else {
        ?>
        <table class="wp-list-table widefat fixed striped pages">
            <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All' ) ?></label>
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <th class="column-primary"><?php _e('ID', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Type', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Status', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Currency rate', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Action', 'woo-all-in-one-currency'); ?></th>
            </tr>
            </thead>

            <tbody id="the-list">

            </tbody>
            <?php
            foreach ($currency_rules as $currency_code => $currency_rule) {
                $is_base = false;
                $is_main = false;
                $rates = '';

                if ($currency_code === $base_currency) {
                    $is_base = true;
                    $rates = '1.00';
                } else {
                    if (!empty($currency_rule['rates'])) {
                        foreach ($currency_rule['rates'] as $rate) {
                            if ('all_products' === $rate['apply']) {
                                $rates = $rate['rate'];
                            }
                        }
                    }
                }

                if (!empty($currency_rule['main'])) {
                    $is_main = true;
                }

                ?>
                <tr>
                    <th class="check-column">
                        <input id="cb-select-<?php echo $currency_code; ?>" type="checkbox" value="<?php echo $currency_code; ?>">
                    </th>

                    <td class="column-primary has-row-actions">
                        <a href="?page=wooaiocurrency&tab=currency&currency_code=<?php echo $currency_code; ?>">
                            <?php echo $currency_rule['title'] ?>
                        </a>

                        <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                    </td>

                    <td data-colname="<?php _e('Type', 'woo-all-in-one-discount'); ?>">
                        <?php
                        if (!$is_base) {
                            ?>
                            <button
                                    id="make-base-currency-<?php echo $currency_code; ?>"
                                    class="button button-primary button-small make-base-currency-rule"
                                    type="button"
                                    data-id="<?php echo $currency_code; ?>"
                                    data-single="no"
                                    data-confirm="<?php _e('Are you sure you want to set this currency as base currency for this site?', 'woo-all-in-one-currency'); ?>"
                                    <?php echo empty($rates) ? ' disabled' : ''; ?>
                            ><?php _e('Make base currency', 'woo-all-in-one-currency'); ?></button>
                            <?php
                        } else {
                            ?>
                            <?php _e('Base currency', 'woo-all-in-one-currency'); ?>
                            <?php
                        }
                        ?>
                    </td>

                    <td data-colname="<?php _e('Status', 'woo-all-in-one-discount'); ?>">
                        <?php
                        if (!$is_main) {
                            ?>
                            <button
                                    id="make-main-currency-<?php echo $currency_code; ?>"
                                    class="button button-primary button-small make-main-currency-rule"
                                    type="button"
                                    data-id="<?php echo $currency_code; ?>"
                                    data-single="no"
                                    data-confirm="<?php _e('Are you sure you want to set this currency as main currency for this site?', 'woo-all-in-one-currency'); ?>"
                                <?php echo empty($rates) ? ' disabled' : ''; ?>
                            ><?php _e('Make main currency', 'woo-all-in-one-currency'); ?></button>
                            <?php
                        } else {
                            ?>
                            <?php _e('Main site currency', 'woo-all-in-one-currency'); ?>
                            <?php
                        }
                        ?>
                    </td>

                    <td data-colname="<?php _e('Currency rate', 'woo-all-in-one-discount'); ?>">
                        <?php
                        if (empty($rates)) {
                            _e('Rate not set', 'woo-all-in-one-currency');
                        } else {
                            echo $rates;
                        }
                        ?>
                    </td>

                    <td data-colname="<?php _e('Action', 'woo-all-in-one-discount'); ?>">
                        <?php
                        if (!$is_base) {
                            ?>
                            <button
                                    id="delete-currency-<?php echo $currency_code; ?>"
                                    class="button button-small delete-currency-rule"
                                    type="button"
                                    data-id="<?php echo $currency_code; ?>"
                                    data-single="no"
                                    data-confirm="<?php _e('Are you sure you want to delete this discount rule?', 'woo-all-in-one-currency'); ?>"
                            ><?php _e('Delete', 'woo-all-in-one-currency'); ?></button>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tfoot>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All' ) ?></label>
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <th class="column-primary"><?php _e('ID', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Type', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Status', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Currency rate', 'woo-all-in-one-currency'); ?></th>
                <th><?php _e('Action', 'woo-all-in-one-currency'); ?></th>
            </tr>
            </tfoot>
        </table>

        <h4><?php _e('Explanation', 'woo-all-in-one-currency'); ?>:</h4>
        <p>
            <?php _e('Base currency is a currency which your product saved in DB. Base currency rate always be equal 1.', 'woo-all-in-one-currency'); ?>
        </p>
        <?php
    }
    ?>
</div>