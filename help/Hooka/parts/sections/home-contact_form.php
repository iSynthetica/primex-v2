<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.09.18
 * Time: 12:41
 */

if(empty($content)) {
    return;
}

$section_content = $content['section_contact_form'];

$toggleId = $content['section_id'] . '_' . uniqid();

snth_before_home_section_content($content);

?>
    <div class="row">
        <div class="col-xs-12">
            <?php echo wpautop($section_content['text']); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
            <div id="<?php echo $toggleId ?>" class="toggle-container">
                <?php echo do_shortcode($section_content['form']) ?>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-mod btn-border btn-medium btn-round toggle-control" data-toggle="<?php echo $toggleId ?>">
                    <?php echo $section_content['button'] ? $section_content['button'] : __('Open', 'snthwp') ?>
                </button>
            </div>
        </div>
    </div>
<?php

snth_after_home_section_content();
