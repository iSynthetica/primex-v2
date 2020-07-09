<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
</table>

<h3 class="wc-settings-sub-title"><?php echo wp_kses_post( $data['title'] ); ?></h3>

<?php if ( ! empty( $data['description'] ) ) : ?>
    <p><?php echo wp_kses_post( $data['description'] ); ?></p>
<?php endif; ?>

<table class="form-table">

<tr valign="top">
    <td class="forminp" id="local_pickup_locations">
        <fieldset class="wc_input_table_wrapper">
            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
            <table class="widefat wc_input_table sortable" cellspacing="0">
                <thead>
                <tr>
                    <th class="sort">&nbsp;</th>
                    <th class="state"><?php _e( 'State / County', 'woocommerce' ); ?></th>
                    <th class="city"><?php _e( 'Town / City', 'woocommerce' ); ?></th>
                    <th class="address"><?php _e( 'Street address', 'woocommerce' ); ?></th>
                </tr>
                </thead>

                <tbody class="pickup_locations">
                <?php
                if (!empty($local_pickup_locations) && is_array($local_pickup_locations)) {
                    $i = 0;
                    foreach ($local_pickup_locations as $pickup_location) {
                        ?>
                        <tr class="pickup_location">
                            <td class="sort"></td>
                            <td><input type="text" name="pickup_location_state[<?php echo $i ?>]" value="<?php echo $pickup_location['pickup_location_state'] ?>" /></td>
                            <td><input type="text" name="pickup_location_city[<?php echo $i ?>]" value="<?php echo $pickup_location['pickup_location_city'] ?>" /></td>
                            <td><input type="text" name="pickup_location_address[<?php echo $i ?>]" value="<?php echo $pickup_location['pickup_location_address'] ?>" /></td>
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
                    jQuery('#local_pickup_locations').on( 'click', 'a.add', function(){

                        var size = jQuery('#local_pickup_locations').find('tbody .pickup_location').length;

                        jQuery('<tr class="pickup_location">\
									<td class="sort"></td>\
									<td><input type="text" name="pickup_location_state[' + size + ']" /></td>\
									<td><input type="text" name="pickup_location_city[' + size + ']" /></td>\
									<td><input type="text" name="pickup_location_address[' + size + ']" /></td>\
								</tr>').appendTo('#local_pickup_locations table tbody');

                        return false;
                    });
                });
            </script>
        </fieldset>
    </td>
</tr>

    <?php
//    if (!empty($this->pickup_locations)) {
//        var_dump($this->pickup_locations);
//    }
    ?>
