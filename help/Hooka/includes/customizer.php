<?php
/**
 * Add functionality to customizer
 *
 * @package Hooka/Includes
 *
 * @link https://codex.wordpress.org/Theme_Customization_API
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add custom logos to customizer settings
 *
 * @param $wp_customize
 */
function snth_theme_logos_customizer_settings( $wp_customize )
{
    $theme_logos = apply_filters( 'snth_custom_logos', array() );

    if ( empty($theme_logos) ) {
        return;
    }

    foreach ($theme_logos as $theme_logo => $args) {
        $wp_customize->add_setting('snth_' . $theme_logo);

        $label = !empty( $args['label'] ) ? $args['label'] : $theme_logo;
        $description = !empty( $args['description'] ) ? $args['description'] : '';

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'snth_' . $theme_logo,
                array(
                    'label' => $label,
                    'description' => $description,
                    'section' => 'title_tagline',
                    'settings' => 'snth_' . $theme_logo,
                )
            )
        );
    }
}
add_action('customize_register', 'snth_theme_logos_customizer_settings');