<?php
/**
 * Single Page Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <!-- About Section -->
    <section class="page-section">
        <div class="container relative">
            <?php the_content(); ?>
        </div>
    </section>
    <!-- End About Section -->
</article>
