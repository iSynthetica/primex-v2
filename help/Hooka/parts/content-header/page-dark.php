<?php
/**
 * Default Single Page Header
 *
 * @package Hooka/Parts/ContentHeader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$header = !empty($header) ? $header : 'page';

global $post;

if (has_post_thumbnail()) {
    $data_background = get_the_post_thumbnail_url( null, 'thumb_1920_1080_cr' );
} else {
    $data_background = SNTH_IMAGES_URL . '/full-width-images/section-bg-10.jpg';
}

$subtitle = get_field('subtitle', $post->ID);

?>

<!-- Head Section -->
<header
    class="article-header page-section bg-dark-alfa-50 parallax-3"
    data-background="<?php echo $data_background; ?>"
>
    <div class="relative container align-left">
        <div class="row">
            <div class="col-md-8">
                <?php the_title('<h1 class="hs-line-11 font-alt mb-20 mb-xs-0">', '</h1>') ?>

                <?php
                if(!empty($subtitle)) {
                    ?>
                    <div class="hs-line-4 font-alt">
                        <?php echo $subtitle; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="col-md-4 mt-30">
                <?php snth_the_breadcrumbs(); ?>
            </div>
        </div>
    </div>
</header>
<!-- End Head Section -->