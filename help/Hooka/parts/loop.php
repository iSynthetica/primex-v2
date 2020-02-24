<?php
/**
 * Single Post template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$content = !empty($content) ? $content : 'archive';
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php snth_show_template('content/'.$content.'.php') ?>
    </main>
</div>