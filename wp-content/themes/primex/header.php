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

    <!-- Top Bar
    ============================================= -->
    <div id="top-bar">

        <div class="container-fullwidth clearfix">

            <div class="col_half nobottommargin d-none d-md-block">

                <p class="nobottommargin"><strong>Call:</strong> 1800-547-2145 | <strong>Email:</strong> info@canvas.com</p>

            </div>

            <div class="col_half col_last fright nobottommargin">

                <!-- Top Links
                ============================================= -->
                <div class="top-links">
                    <ul>
                        <li><a href="#">USD</a>
                            <ul>
                                <li><a href="#">EUR</a></li>
                                <li><a href="#">AUD</a></li>
                                <li><a href="#">GBP</a></li>
                            </ul>
                        </li>
                        <li><a href="#">EN</a>
                            <ul>
                                <li><a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/icons/flags/french.png" alt="French"> FR</a></li>
                                <li><a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/icons/flags/italian.png" alt="Italian"> IT</a></li>
                                <li><a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/icons/flags/german.png" alt="German"> DE</a></li>
                            </ul>
                        </li>
                        <li><a href="#">Login</a>
                            <div class="top-link-section">
                                <form id="top-login" role="form">
                                    <div class="input-group" id="top-login-username">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="icon-user"></i></div>
                                        </div>
                                        <input type="email" class="form-control" placeholder="Email address" required="">
                                    </div>
                                    <div class="input-group" id="top-login-password">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="icon-key"></i></div>
                                        </div>
                                        <input type="password" class="form-control" placeholder="Password" required="">
                                    </div>
                                    <label class="checkbox">
                                        <input type="checkbox" value="remember-me"> Remember me
                                    </label>
                                    <button class="btn btn-danger btn-block" type="submit">Sign in</button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div><!-- .top-links end -->

            </div>

        </div>

    </div><!-- #top-bar end -->

    <!-- Header
    ============================================= -->
    <header id="header" class="sticky-style-2">

        <div class="container-fullwidth clearfix">

            <!-- Logo
            ============================================= -->
            <div id="logo">
                <a href="<?php echo get_home_url() ?>" class="standard-logo" data-dark-logo="<?php echo SNTH_IMAGES_URL ?>/logo.png"><img src="<?php echo SNTH_IMAGES_URL ?>/logo.png" alt="Primex Logo"></a>
                <a href="<?php echo get_home_url() ?>" class="retina-logo" data-dark-logo="<?php echo SNTH_IMAGES_URL ?>/logo.png"><img src="<?php echo SNTH_IMAGES_URL ?>/logo.png" alt="Primex Logo"></a>
            </div><!-- #logo end -->

            <ul class="header-extras">
                <li>
                    <i class="i-medium i-circled i-bordered icon-thumbs-up2 nomargin"></i>
                    <div class="he-text">
                        Original Brands
                        <span>100% Guaranteed</span>
                    </div>
                </li>
                <li>
                    <i class="i-medium i-circled i-bordered icon-truck2 nomargin"></i>
                    <div class="he-text">
                        Free Shipping
                        <span>for $20 or more</span>
                    </div>
                </li>
                <li>
                    <i class="i-medium i-circled i-bordered icon-undo nomargin"></i>
                    <div class="he-text">
                        30-Day Returns
                        <span>Completely Free</span>
                    </div>
                </li>
            </ul>

        </div>

        <div id="header-wrap">

            <?php snth_show_template('header/nav-style2.php'); ?>

        </div>

    </header><!-- #header end -->
