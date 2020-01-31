<?php
/**
 * Template Name: Landing Page
 */

$layout = 'fullwidth';
?>

<?php get_header();?>

<?php
/* Start the Loop */
while ( have_posts() ) :
    the_post();
    $page_sections = get_field('page_sections');

    ?>
    <!-- Page Title ============================================= -->
    <section id="page-title">
        <div class="container clearfix">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            <?php
            if (!empty($subtitle)) {
                ?>
                <span>Page Content on the Left &amp; Sidebar on the Right</span>
                <?php
            }
            ?>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Pages</a></li>
                <li class="breadcrumb-item active" aria-current="page">Right Sidebar</li>
            </ol>
        </div>
    </section><!-- #page-title end -->

    <!-- Content ============================================= -->
    <section id="content">
        <div class="content-wrap nomargin nopadding">
            <?php if (!empty($page_sections)) snth_show_sections($page_sections); ?>
        </div>
    </section>
    <?php

endwhile; // End of the loop.
?>

<?php get_footer(); ?>
