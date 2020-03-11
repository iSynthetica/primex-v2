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

            <div id="secondary-nav" class="col_half nobottommargin d-none d-md-block">

                <!-- Top Links
					============================================= -->
                <?php snth_top_bar_nav(); ?>

            </div>

            <div id="additional-nav" class="col_half col_last fright nobottommargin">

                <!-- Top Links
                ============================================= -->
                <div class="top-links">
                    <ul>
                        <?php
                        if (function_exists('wooaiocurrency_currency_switcher')) {
                            wooaiocurrency_currency_switcher();
                        }

                        if (false) {
                            ?>
                            <li><a href="#">EN</a>
                                <ul class="sub-small">
                                    <li><a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/icons/flags/french.png" alt="French"> FR</a></li>
                                    <li><a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/icons/flags/italian.png" alt="Italian"> IT</a></li>
                                    <li><a href="#"><img src="<?php echo SNTH_IMAGES_URL ?>/canvas/icons/flags/german.png" alt="German"> DE</a></li>
                                </ul>
                            </li>
                            <?php
                        }
                        if (is_user_logged_in()) {
                            ?>
                            <li id="my-account"><a href="#">
                                    <i class="far fa-user-circle"></i><span><?php _e('My account', 'primex'); ?></span>
                                </a>
                                <ul>
                                    <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
                                        <li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
                                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li id="my-account"><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>"><i class="fas fa-sign-in-alt"></i><span><?php _e('Login', 'primex'); ?></span></a></li>
                            <?php
                        }
                        ?>
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

            <?php echo do_shortcode('[snth_phones_header]') ?>

        </div>

        <div id="header-wrap">

            <?php snth_show_template('header/nav-style2.php'); ?>

        </div>

    </header><!-- #header end -->
