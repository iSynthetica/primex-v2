<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
</table>

<h3 class="wc-settings-sub-title"><?php echo wp_kses_post( $data['title'] ); ?></h3>

<?php if ( ! empty( $data['description'] ) ) : ?>
    <p><?php echo wp_kses_post( $data['description'] ); ?></p>
<?php endif; ?>

<table class="form-table">

<tr valign="top">
    <td class="forminp" id="courier_settings">
        <fieldset class="wc_input_table_wrapper">
            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
            <table class="widefat wc_input_table sortable" cellspacing="0">
                <thead>
                <tr>
                    <th class="sort">&nbsp;</th>
                    <th class="state"><?php _e( 'State / County', 'woocommerce' ); ?></th>
                    <th class="city"><?php _e( 'Town / City', 'woocommerce' ); ?></th>
                    <th class="city"><?php _e( 'Cost', 'woocommerce' ); ?></th>
                </tr>
                </thead>

                <tbody class="pickup_locations">
                <?php
                if (!empty($courier_settings) && is_array($courier_settings)) {
                    $i = 0;
                    foreach ($courier_settings as $courier_setting) {
                        $cost = !empty($courier_setting['courier_setting_cost']) ? $courier_setting['courier_setting_cost'] : '0';
                        ?>
                        <tr class="courier_setting">
                            <td class="sort"></td>
                            <td><input type="text" name="courier_setting_state[<?php echo $i ?>]" value="<?php echo $courier_setting['courier_setting_state'] ?>" /></td>
                            <td><input type="text" name="courier_setting_city[<?php echo $i ?>]" value="<?php echo $courier_setting['courier_setting_city'] ?>" /></td>
                            <td><input type="number" name="courier_setting_cost[<?php echo $i ?>]" value="<?php echo $cost ?>" /></td>
                        </tr>
                        <?php
                        $i++;
                    }
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="4"><a href="#" class="add button"><?php esc_html_e( '+ Add location', 'woocommerce' ); ?></a> <a href="#" class="remove_rows button"><?php esc_html_e( 'Remove selected account(s)', 'woocommerce' ); ?></a></th>
                </tr>
                </tfoot>
            </table>

            <script type="text/javascript">
                jQuery(function() {
                    jQuery('#courier_settings').on( 'click', 'a.add', function(){

                        var size = jQuery('#courier_settings').find('tbody .courier_setting').length;

                        jQuery('<tr class="courier_setting">\
									<td class="sort"></td>\
									<td><input type="text" name="courier_setting_state[' + size + ']" /></td>\
									<td><input type="text" name="courier_setting_city[' + size + ']" /></td>\
									<td><input type="number" name="courier_setting_cost[' + size + ']" value="0" /></td>\
								</tr>').appendTo('#courier_settings table tbody');

                        return false;
                    });
                });
            </script>
        </fieldset>
    </td>
</tr>
