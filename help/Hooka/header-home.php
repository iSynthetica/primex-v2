<?php
/**
 * The Header for our theme
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html class="no-js"  <?php language_attributes(); ?>>
<head>
    <meta charset="utf-8">

    <!-- Force IE to use the latest rendering engine available -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- If Site Icon isn't set in customizer -->
    <?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
        <!-- Icons & Favicons -->
        <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
        <link href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-icon-touch.png" rel="apple-touch-icon" />
    <?php } ?>

    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

    <meta name="author" content="Synthetica">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127940506-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-‎127940506-1');
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class('appear-animate'); ?>>

    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">Loading...</div>
    </div>
    <!-- End Page Loader -->

    <!-- Page Wrap -->
    <div class="page" id="top">

        <?php
        global $post;

        if (has_post_thumbnail()) {
            $data_background = get_the_post_thumbnail_url( null, 'thumb_1920_1080_cr' );
        } else {
            $data_background = SNTH_IMAGES_URL . '/full-width-images/section-bg-1.jpg';
        }
        ?>

        <!-- Home Section -->
<!--        <section class="home-section bg-dark-alfa-30test" data-background="--><?php //echo $data_background; ?><!--" id="home">-->
        <section class="home-section bg-dark-alfa-30" data-background="<?php echo $data_background; ?>" id="home">
            <div class="js-height-full container">

                <!-- Hero Content -->
                <div class="home-content">
                    <div class="home-text">

                        <h1 class="hs-line-15 font-alt mb-80 mb-xs-30 mt-50 mt-sm-0">
                            <?php echo get_the_title($post); ?>
                        </h1>

                        <div class="hs-line-16 font-alt">
                            <?php echo get_field('subtitle', $post->ID) ?>
                        </div>

                    </div>
                </div>
                <!-- End Hero Content -->

                <!-- Scroll Down -->
                <div class="local-scroll">
                    <a href="#primary" class="scroll-down"><i class="fas fa-angle-down scroll-down-icon"></i></a>
                </div>
                <!-- End Scroll Down -->

            </div>
        </section>
        <!-- End Home Section -->

        <?php snth_show_template('nav-main-nav-home.php') ?>

