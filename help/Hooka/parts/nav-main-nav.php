<?php
/**
 * Remove useless WP outputs
 *
 * @package Hooka/Parts
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Navigation panel -->
<nav class="main-nav dark js-stick">
    <div class="full-wrapper relative clearfix">

        <!-- Logo ( * your text or image into link tag *) -->
        <?php snth_theme_logo_home_nav('alt_logo'); ?>

        <div class="mobile-nav">
            <i class="fas fa-bars"></i>
        </div>

        <div class="mobile-cart" data-location="<?php echo esc_url( wc_get_cart_url() ) ?>">
            <i class="fas fa-shopping-basket"></i>
        </div>

        <?php
        // if (is_user_logged_in() && current_user_can('administrator')) {
            $currency = apply_filters( 'wcml_price_currency', NULL );

            if (!$currency) {
                $currency_label = __('uah', 'snthwp');
            } elseif ('EUR' === $currency) {
                $currency_label = '€';
            } elseif ('USD' === $currency) {
                $currency_label = '$';
            }

            $language = apply_filters( 'wpml_current_language', NULL );
            $language_label = strtoupper($language);
            ?>
            <div class="mobile-currency mobile-dropdown">
                <i class="fas mobile-dropdown-control">
                    <?php echo $currency_label ?>
                </i>

                <div class="mobile-dropdown-content">
                    <?php echo do_shortcode('[currency_switcher format="%symbol%"]') ?>
                </div>
            </div>

            <div class="mobile-language mobile-dropdown">
                <i class="fas mobile-dropdown-control">
                    <?php echo $language_label ?>
                </i>

                <div class="mobile-dropdown-content">
                    <?php echo do_shortcode('[wpml_language_switcher flags=1 native=1 translated=1][/wpml_language_switcher]') ?>
                </div>
            </div>
            <?php
        // }
        ?>

        <!-- Main Menu -->
        <div class="inner-nav desktop-nav">
            <?php snth_main_nav() ?>
            <?php // snth_show_template('nav/main-menu-html.php') ?>
        </div>
        <!-- End Main Menu -->

    </div>
</nav>
<!-- End Navigation panel -->