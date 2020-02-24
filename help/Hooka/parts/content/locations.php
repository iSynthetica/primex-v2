<?php
/**
 * Contacts Template Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$map_markers = snth_get_map_markers('hookah');

$map_center = $map_markers['center'];

unset($map_markers['center']);

$page_map_center = get_field('gmap_center');
$page_map_zoom = get_field('gmap_zoom');

if(!empty($page_map_center)) {
    $map_center['lat'] = $page_map_center['lat'];
    $map_center['lng'] = $page_map_center['lng'];
}

$icon = SNTH_IMAGES_URL . '/map-marker.png';

wp_enqueue_script('gmapLocations');

wp_localize_script('gmapLocations', 'jointsMapObj', array(
    'markers'   => $map_markers,
    'center'    => $map_center,
    'icon'      =>  $icon,
    'zoom'      =>  $page_map_zoom ? (int)$page_map_zoom : 17,
));

$add_to_map = get_field('add_to_map');

$toggleId = 'add_to_map_' . uniqid();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <section class="page-section pt-0 pb-0">
        <div id="map-canvas" class="height-full">

        </div>
    </section>

    <section class="page-section pt-20 pb-20 pt-xs-10 pb-xs-10 ">
        <div class="container relative">
            <div class="row">
                <div class="col-xs-12">
                    <?php the_content(); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
                    <div id="<?php echo $toggleId ?>" class="toggle-container">
                        <?php echo wpautop($add_to_map['text']); ?>

                        <?php echo do_shortcode($add_to_map['forma']) ?>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-mod btn-border btn-medium btn-round toggle-control" data-toggle="<?php echo $toggleId ?>">
                            <?php echo $add_to_map['button'] ? $add_to_map['button'] : __('Open', 'snthwp') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</article>
