<?php get_header(); ?>
<?php
/* Start the Loop */
while ( have_posts() ) :
    the_post();
    $page_sections = get_field('page_sections');

    ?>
    <section id="content">

        <div class="content-wrap nomargin nopadding">

            <?php if (!empty($page_sections)) snth_show_sections($page_sections); ?>

        </div>

    </section>
    <?php
endwhile; // End of the loop.
?>

<?php get_footer(); ?>
