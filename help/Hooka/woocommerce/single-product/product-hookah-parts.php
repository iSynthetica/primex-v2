<?php
/**
 * Single Product Thumbnails
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-thumbnails.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.2
 */

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product, $post;

if (!snth_is_product_hookah()) {
    return;
}

$hookah_groups = get_field('hookah_groups', $post->ID);

if (empty($hookah_groups)) {
    $hookah_groups = get_field('hookah_groups', 'options');
}

if (empty($hookah_groups)) {
    return;
}

$curr_lang = ICL_LANGUAGE_CODE;
$attr_prefix = '';

?>
<div class="woocommerce-product-parts__row">
        <?php
        foreach ($hookah_groups as $groups) {
            $is_attribute = $groups['is_attribute'];

            $products_array = array();

            if ($is_attribute) {
                $gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );

                // TODO - Code optimization
                $thumbnail_size = array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] );
                $full_size = 'single_product_zoom';

                $thumbnail_src = wp_get_attachment_image_src( $groups['image'], $thumbnail_size);
                $full_src = wp_get_attachment_image_src( $groups['image'], $full_size );
                $src_set = wp_get_attachment_image_srcset( $groups['image'], 'woocommerce_thumbnail' );

                $default_data = array ();
                $default_data['src'] = $thumbnail_src[0];
                $default_data['srcset'] = $src_set;
                $default_data['href'] = $full_src[0];


                $attributes_data = array();

                foreach ($groups['group'] as $parts) {
                    if ('ru' !== $curr_lang) {
                        $attr_prefix = '-' . $curr_lang;
                    }
                    $products_array[] = $parts['product'];

                    $part = get_post($parts['product']);

                    $thumbnailId = get_post_thumbnail_id( $part->ID );

                    $thumbnail_src = wp_get_attachment_image_src( $thumbnailId, $thumbnail_size);
                    $full_src = wp_get_attachment_image_src( $thumbnailId, $full_size );
                    $src_set = wp_get_attachment_image_srcset($thumbnailId, 'woocommerce_thumbnail');

                    $attributes_data[$part->post_name . $attr_prefix] = array(
                        'src' => $thumbnail_src[0],
                        'srcset' => $src_set,
                        'href' => $full_src[0],
                    );
                }
            }
            ?>
            <div class="product-part__holder row <?php echo $is_attribute ? ' attribute_pa_holder attribute_pa_' . $groups['attribute'] . '_holder' : '' ?>">
                <div class="col-xs-12">
                    <?php
                    if ($is_attribute) {
                        $attachment_id = $groups['image'];
                        echo snth_wc_get_gallery_image_html(
                            $groups['image'],
                            false,
                            array(
                                'link_class'    => 'disabled',
                                'holder_class'  => '',
                                'holder_data'   => array(
                                    'data-default' => htmlspecialchars( wp_json_encode( $default_data ) ),
                                    'data-attributes' => htmlspecialchars( wp_json_encode( $attributes_data ) )
                                )
                            )
                        );
                    } else {
                        $attachment_id = $groups['image'];
                        if (!$attachment_id) {
                            $attachment_id = wc_get_product($groups['group'][0]['product'])->get_image_id();
                        }
                        echo snth_wc_get_gallery_image_html(
                            $attachment_id,
                            false,
                            array(
                                'link_class'    => 'woocommerce-gallery',
                                'holder_class'  => '',
                            )
                        );
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
</div>
