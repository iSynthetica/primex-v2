<?php
/**
 * The template for displaying the header
 *
 * @package WordPress
 * @subpackage Prime-X
 * @since Prime-X 1.0
 */
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="SemiColonWeb" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Document Title
    ============================================= -->
    <title>Canvas | The Multi-Purpose HTML5 Template</title>
    <?php wp_head(); ?>
</head>

<body class="stretched">

<!-- Document Wrapper
============================================= -->
<div id="wrapper" class="clearfix">

    <!-- Header
    ============================================= -->
    <header id="header" class="full-header dark">

        <div id="header-wrap">

            <div class="container clearfix">

                <div id="primary-menu-trigger"><i class="icon-reorder"></i></div>

                <!-- Logo
                ============================================= -->
                <div id="logo">
                    <a href="<?php echo get_home_url() ?>" class="standard-logo" data-dark-logo="<?php echo SNTH_IMAGES_URL ?>/canvas/logo-dark.png"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/logo.png" alt="Canvas Logo"></a>
                    <a href="<?php echo get_home_url() ?>" class="retina-logo" data-dark-logo="<?php echo SNTH_IMAGES_URL ?>/canvas/logo-dark@2x.png"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/logo@2x.png" alt="Canvas Logo"></a>
                </div><!-- #logo end -->

                <?php snth_show_template('header/nav.php'); ?>

            </div>

        </div>

    </header><!-- #header end -->
