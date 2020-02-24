<?php
/**
 * Archive Page Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$header = !empty($header) ? $header : 'archive';
$loop = !empty($loop) ? $loop : 'single-columns';

?>

<?php snth_show_template('content-header/'.$header.'.php') ?>

<!-- Search Section -->
<section class="small-section">
    <div class="container relative">
        <!-- Search -->
        <?php get_search_form(); ?>
        <!-- End Search -->
    </div>
</section>
<!-- End Search Section -->


<!-- Divider -->
<hr class="mt-0 mb-0"/>
<!-- End Divider -->


<!-- Section -->
<section class="page-section">
    <div class="container relative">

        <?php if (have_posts()) : ?>

            <div class="row multi-columns-row">
                <?php while (have_posts()) : the_post(); ?>
                    <?php snth_show_template('loop/'.$loop.'.php') ?>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <a href=""><i class="fa fa-angle-left"></i></a>
                <a href="" class="active">1</a>
                <a href="">2</a>
                <a href="">3</a>
                <a class="no-active">...</a>
                <a href="">9</a>
                <a href=""><i class="fa fa-angle-right"></i></a>
            </div>
            <!-- End Pagination -->

        <?php else : ?>

            <?php //get_template_part( 'parts/content', 'missing' ); ?>

        <?php endif; ?>
    </div>
</section>
<!-- End Section -->


<!-- Divider -->
<hr class="mt-0 mb-0"/>
<!-- End Divider -->


<!-- Widgets Section -->
<section class="page-section">
    <div class="container relative">

        <div class="row multi-columns-row">

            <div class="col-sm-6 col-md-3 col-lg-3">

                <!-- Widget -->
                <div class="widget">

                    <h5 class="widget-title font-alt">Categories</h5>

                    <div class="widget-body">
                        <ul class="clearlist widget-menu">
                            <li>
                                <a href="#" title="">Branding</a>
                                <small>
                                    - 7
                                </small>
                            </li>
                            <li>
                                <a href="#" title="">Design</a>
                                <small>
                                    - 15
                                </small>
                            </li>
                            <li>
                                <a href="#" title="">Development</a>
                                <small>
                                    - 3
                                </small>
                            </li>
                            <li>
                                <a href="#" title="">Photography</a>
                                <small>
                                    - 5
                                </small>
                            </li>
                            <li>
                                <a href="#" title="">Other</a>
                                <small>
                                    - 1
                                </small>
                            </li>
                        </ul>
                    </div>

                </div>
                <!-- End Widget -->

            </div>

            <div class="col-sm-6 col-md-3 col-lg-3">

                <!-- Widget -->
                <div class="widget">

                    <h5 class="widget-title font-alt">Tags</h5>

                    <div class="widget-body">
                        <div class="tags">
                            <a href="">Design</a>
                            <a href="">Portfolio</a>
                            <a href="">Digital</a>
                            <a href="">Branding</a>
                            <a href="">Theme</a>
                            <a href="">Clean</a>
                            <a href="">UI & UX</a>
                            <a href="">Love</a>
                        </div>
                    </div>

                </div>
                <!-- End Widget -->

            </div>

            <div class="col-sm-6 col-md-3 col-lg-3">

                <!-- Widget -->
                <div class="widget">

                    <h5 class="widget-title font-alt">Archive</h5>

                    <div class="widget-body">
                        <ul class="clearlist widget-menu">
                            <li>
                                <a href="#" title="">January 2015</a>
                            </li>
                            <li>
                                <a href="#" title="">February 2014</a>
                            </li>
                            <li>
                                <a href="#" title="">January 2014</a>
                            </li>
                            <li>
                                <a href="#" title="">December 2013</a>
                            </li>
                        </ul>
                    </div>

                </div>
                <!-- End Widget -->

            </div>

            <div class="col-sm-6 col-md-3 col-lg-3">

                <!-- Widget -->
                <div class="widget">

                    <h5 class="widget-title font-alt">Text widget</h5>

                    <div class="widget-body">
                        <div class="widget-text clearfix">

                            <img src="<?php echo SNTH_IMAGES_URL; ?>/user-avatar.png" alt="" width="70" class="img-circle left img-left">

                            Consectetur adipiscing elit. Quisque magna ante eleifend eleifend.
                            Purus ut dignissim consectetur, nulla erat ultrices purus, ut consequat sem elit non sem.
                            Quisque magna ante eleifend eleifend.

                        </div>
                    </div>

                </div>
                <!-- End Widget -->

            </div>

        </div>

    </div>
</section>
<!-- End Widgets Section -->
