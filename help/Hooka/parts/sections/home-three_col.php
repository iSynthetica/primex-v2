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

$section_content = $content['section_three_col'];

snth_before_home_section_content($content);

?>
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <?php echo wpautop($section_content['content_1']); ?>
        </div>

        <div class="col-sm-6 col-md-4">
            <?php echo wpautop($section_content['content_2']); ?>
        </div>

        <div class="col-sm-6 col-md-4">
            <?php echo wpautop($section_content['content_3']); ?>
        </div>
    </div>
<?php

snth_after_home_section_content();