<?php
/**
 * @var $content
 */

// var_dump($content);
?>

<div class="row clearfix">
    <div class="col-md-6 col-lg-3">
        <?php echo wpautop($content['column_1']) ?>
    </div>

    <div class="col-md-6 col-lg-3">
        <?php echo wpautop($content['column_2']) ?>
    </div>

    <div class="col-md-6 col-lg-3">
        <?php echo wpautop($content['column_3']) ?>
    </div>

    <div class="col-md-6 col-lg-3">
        <?php echo wpautop($content['column_4']) ?>
    </div>
</div>
