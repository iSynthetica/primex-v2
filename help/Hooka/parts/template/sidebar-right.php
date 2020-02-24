<?php
/**
 * Single Post template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$header = !empty($header) ? $header : 'page';
$content = !empty($content) ? $content : 'page';
$sidebar = !empty($sidebar) ? $sidebar : 'page';
?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <?php snth_show_template('content-header/'.$header.'.php') ?>

    <section class="page-section">
        <div class="container relative">
            <div class="row">
                <div class="col-sm-8">
                    <div id="primary" class="content-area">
                        <main id="main" class="site-main" role="main">
                            <?php snth_show_template('content/'.$content.'.php') ?>
                        </main>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-sm-4 col-md-3 col-md-offset-1">
                    <aside id="secondary" class="widget-area" role="complementary">
                        <?php snth_show_template('sidebar/'.$sidebar.'.php') ?>
                    </aside>
                </div>
                <!-- End Sidebar -->
            </div>
        </div>
    </section>
<?php endwhile; endif; ?>