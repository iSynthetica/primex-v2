<?php
/**
 * @var $content
 */

// var_dump($content);
?>

<div class="row clearfix">
    <div class="col-md-4">
        <?php echo wpautop($content['column_1']) ?>
    </div>

    <div class="col-md-4">
        <?php echo wpautop($content['column_2']) ?>
    </div>

    <div class="col-md-4">
        <?php echo wpautop($content['column_3']) ?>
    </div>
</div>
