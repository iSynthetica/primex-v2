<?php extract($berocket_query_var_title); ?>
<div class="berocket_aapf_widget">
    <button type="button" class="button button-reveal button-small btn-block berocket_aapf_widget_update_button<?php if ( ! empty($is_hide_mobile) ) echo ' berocket_aapf_hide_mobile' ?>">
        <i class="fas fa-filter"></i>
        <span><?php echo berocket_isset($title) ?></span>
    </button>
</div>
