<?php
/**
 * The Footer for our theme
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

        <!-- Foter -->
        <footer class="page-section bg-gray-lighter footer small-section bg-dark pt-30 pb-10">
            <div class="container">

                <!-- Footer Logo -->
                <div class="local-scroll mb-40 mb-xs-30 wow fadeInUp" data-wow-duration="1.5s">
                    <a href="#top" style="display:inline-block;max-width: 160px">
                        <?php snth_theme_logo('alt_logo'); ?>
                    </a>
                </div>
                <!-- End Footer Logo -->

                <!-- Footer Text -->
                <div class="footer-text mb-20 mb-xs-10">

                    <!-- Copyright -->
                    <div class="footer-copy font-alt">
                        <div class="footer-contacts">
                            <?php

                            $emails = get_field('emails', 'options');

                            if( !empty($emails) ) {
                                ?>
                                <div class="footer-contacts__item footer-contacts__email">
                                <?php
                                foreach ($emails as $email) {
                                    ?>
                                    <a href="mailto:<?php echo $email[0]['link']; ?>"><?php echo $email[0]['label']; ?></a><br>
                                    <?php

                                }
                                ?>
                                </div>
                                <?php
                            }

                            $phones = get_field('phones', 'options');

                            if( !empty($phones) ) {
                            ?>
                            <div class="footer-contacts__item">
                                <?php
                                foreach ($phones as $phone) {
                                    ?>
                                    <a href="<?php echo $phone[0]['link']; ?>"><?php echo $phone[0]['label']; ?></a><br>
                                    <?php

                                }
                                ?>
                            </div>
                                <?php
                            }

                            $messengers = get_field('messengers', 'options');

                            if( !empty($messengers) ) {
                                $count = count($messengers['item']);
                                $i = 1;
                                foreach ($messengers['item'] as $messenger) {
                                    ?>
                                    <span class="show-on-<?php echo $messenger['use_on'] ?>">
                                    <a href="<?php echo $messenger['link']; ?>">
                                        <small><?php echo $messenger['label']; ?></small>
                                    </a>
                                        <?php
                                        echo $i < $count ? ' | ' : '';
                                        ?>
                                </span>
                                    <?php
                                    $i++;
                                }
                            }
                            ?>

                        </div>
                    </div>
                    <!-- End Copyright -->

                </div>
                <!-- End Footer Text -->

                <!-- Social Links -->
                <?php
                $socials = get_field('social', 'options');

                if(!empty($socials)) {
                    ?>
                    <div class="footer-social-links mb-40 mb-xs-30">
                        <?php
                        foreach ($socials['item'] as $social) {
                            ?>
                            <a href="<?php echo $social['link']; ?>" title="<?php echo $social['label']; ?>" target="_blank"><i class="fab fa-<?php echo $social['icon']; ?>"></i></a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
                <!-- End Social Links -->

                <!-- Footer Text -->
                <div class="footer-text">

                    <!-- Copyright -->
                    <div class="footer-copy font-alt">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                            &copy; <?php bloginfo( 'name' ); ?> | <?php bloginfo( 'description' ) ?> <?php echo date("Y"); ?>
                        </a>
                    </div>
                    <!-- End Copyright -->

                </div>
                <!-- End Footer Text -->

            </div>


            <!-- Top Link -->
            <div class="local-scroll">
                <a href="#top" class="link-to-top"><i class="fa fa-caret-up"></i></a>
            </div>
            <!-- End Top Link -->

        </footer>
        <!-- End Foter -->

        </div>
        <!-- End Page Wrap -->

    <?php wp_footer(); ?>

    </body>
</html>