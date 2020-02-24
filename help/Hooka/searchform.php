<?php
/**
 * The template for displaying search form
 */
 ?>
<form role="search" method="get" class="form-inline form" action="<?php echo home_url( '/' ); ?>">
    <div class="search-wrap">
        <button class="search-button animate" type="submit" title="<?php _e('Start Search', 'snthwp'); ?>">
            <i class="fa fa-search"></i>
        </button>
        <input type="text" class="form-control search-field" placeholder="<?php _e('Search...', 'snthwp'); ?>">
    </div>
</form>