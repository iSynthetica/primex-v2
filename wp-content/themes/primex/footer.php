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

            <div class="col_half">

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

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-facebook">
                        <i class="icon-facebook"></i>
                        <i class="icon-facebook"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-twitter">
                        <i class="icon-twitter"></i>
                        <i class="icon-twitter"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-gplus">
                        <i class="icon-gplus"></i>
                        <i class="icon-gplus"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-pinterest">
                        <i class="icon-pinterest"></i>
                        <i class="icon-pinterest"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-vimeo">
                        <i class="icon-vimeo"></i>
                        <i class="icon-vimeo"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-github">
                        <i class="icon-github"></i>
                        <i class="icon-github"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-yahoo">
                        <i class="icon-yahoo"></i>
                        <i class="icon-yahoo"></i>
                    </a>

                    <a href="#" class="social-icon si-small si-rounded topmargin-sm si-linkedin">
                        <i class="icon-linkedin"></i>
                        <i class="icon-linkedin"></i>
                    </a>
                </div>
            </div>

            <div class="col_one_fourth">
                <div class="widget clearfix">
                    <?php dynamic_sidebar( 'footer4' ); ?>
                </div>
            </div>

            <div class="col_one_fourth col_last">
                <div class="widget clearfix">
                    <?php dynamic_sidebar( 'footer5' ); ?>
                </div>
            </div>
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
