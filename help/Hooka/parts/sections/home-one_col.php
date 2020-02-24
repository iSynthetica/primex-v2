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

$section_content = $content['section_one_col'];

snth_before_home_section_content($content);

?>
    <div class="row">
        <div class="col-xs-12">
            <?php echo wpautop($section_content['content']); ?>
        </div>
    </div>
<?php

snth_after_home_section_content();
