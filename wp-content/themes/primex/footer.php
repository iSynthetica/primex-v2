<?php
/**
 * The template for displaying the footer
 *
 * @package WordPress
 * @subpackage Prime-X
 * @since Prime-X 1.0
 */
?>



<!-- Footer
============================================= -->
<footer id="footer" class="dark">

    <div class="container">

        <!-- Footer Widgets
        ============================================= -->
        <div class="footer-widgets-wrap clearfix">

            <div class="col_two_third">

                <div class="widget clearfix">

                    <img src="<?php echo SNTH_IMAGES_URL ?>/logo.png" alt="" class="footer-logo">

                    <?php dynamic_sidebar( 'footer1' ); ?>

                    <div class="clearfix" style="padding: 10px 0;">
                        <div class="col_half">
                            <?php dynamic_sidebar( 'footer2' ); ?>
                        </div>
                        <div class="col_half col_last">
                            <?php dynamic_sidebar( 'footer3' ); ?>
                        </div>
                    </div>

                    <?php echo do_shortcode('[snth_social]'); ?>
                </div>
            </div>

            <div class="col_one_third col_last">
                <div class="widget clearfix">
                    <?php dynamic_sidebar( 'footer4' ); ?>
                </div>
            </div>

            <?php
            if (false) {
                ?>
                <div class="col_one_fourth col_last">
                    <div class="widget clearfix">
                        <?php dynamic_sidebar( 'footer5' ); ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div><!-- .footer-widgets-wrap end -->
    </div>

    <!-- Copyrights
    ============================================= -->
    <div id="copyrights">

        <div class="container clearfix">

            <div class="col_full nobottommargin center">
                <div class="copyrights-menu copyright-links clearfix">
                    <a href="#">Home</a>/<a href="#">About</a>/<a href="#">Features</a>/<a href="#">Portfolio</a>/<a href="#">FAQs</a>/<a href="#">Contact</a>
                </div>
                <?php echo get_bloginfo('name'); ?> &copy; 2013 - <?php echo date('Y'); ?> All Rights Reserved.
            </div>

        </div>

    </div><!-- #copyrights end -->

</footer><!-- #footer end -->

</div><!-- #wrapper end -->

<!-- Go To Top
============================================= -->
<div id="gotoTop">
    <i class="fas fa-angle-double-up"></i>
</div>

<div class="modal fade" id="product-modal-desc" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-body">
            <div class="modal-content">

            </div>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
