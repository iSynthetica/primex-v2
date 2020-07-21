<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$image_url = AFRSM_PRO_PLUGIN_URL . 'admin/images/right_click.png';
?>
<div class="dotstore_plugin_sidebar">
<?php 
$review_url = '';
$plugin_at = '';
$review_url = esc_url( 'https://wordpress.org/plugins/woo-extra-flat-rate/#reviews' );
$plugin_at = 'WP.org';
?>
    <div class="dotstore-important-link">
        <div class="image_box">
            <img src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/rate-us.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Rate us', 'advanced-flat-rate-shipping-for-woocommerce' );
?> ">
        </div>
        <div class="content_box">
            <h3><?php 
esc_html_e( 'Like This Plugin?', 'advanced-flat-rate-shipping-for-woocommerce' );
?></h3>
            <p><?php 
esc_html_e( 'Your Review is very important to us as it helps us to grow more.', 'advanced-flat-rate-shipping-for-woocommerce' );
?></p>
            <a class="btn_style" href="<?php 
echo  $review_url ;
?>" target="_blank"><?php 
esc_html_e( 'Review Us on ', 'advanced-flat-rate-shipping-for-woocommerce' );
echo  $plugin_at ;
?></a>
        </div>
    </div>
	<?php 
?>
            <div class="dotstore_discount_voucher">
                <span class="dotstore_discount_title"><?php 
esc_html_e( 'Discount Voucher', 'advanced-flat-rate-shipping-for-woocommerce' );
?></span>
                <span class="dotstore-upgrade"><?php 
esc_html_e( 'Upgrade to premium now and get', 'advanced-flat-rate-shipping-for-woocommerce' );
?></span>
                <strong class="dotstore-OFF"><?php 
esc_html_e( '10% OFF', 'advanced-flat-rate-shipping-for-woocommerce' );
?></strong>
                <span class="dotstore-with-code"><?php 
esc_html_e( 'with code', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
			<b><?php 
esc_html_e( 'DOT10', 'advanced-flat-rate-shipping-for-woocommerce' );
?></b></span>
                <a class="dotstore-upgrade"
                   href="<?php 
echo  esc_url( 'www.thedotstore.com/advanced-flat-rate-shipping-method-for-woocommerce' ) ;
?>"
                   target="_blank"><?php 
esc_html_e( 'Upgrade Now!', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
            </div>
			<?php 
?>
    <div class="dotstore-important-link">
        <div class="video-detail important-link">
            <a href="<?php 
echo  esc_url( 'https://www.youtube.com/watch?v=y3Sh6_Qaen0' ) ;
?>" target="_blank">
                <img width="100%"
                     src="<?php 
echo  esc_url( AFRSM_PRO_PLUGIN_URL . 'admin/images/plugin-videodemo.png' ) ;
?>"
                     alt="<?php 
esc_html_e( 'Advanced Flat Rate Shipping For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
            </a>
        </div>
    </div>

    <div class="dotstore-important-link">
        <h2>
            <span class="dotstore-important-link-title"><?php 
esc_html_e( 'Important link', 'advanced-flat-rate-shipping-for-woocommerce' );
?></span>
        </h2>
        <div class="video-detail important-link">
            <ul>
                <li>
                    <img src="<?php 
echo  esc_url( $image_url ) ;
?>">
                    <a target="_blank"
                       href="<?php 
echo  esc_url( 'www.thedotstore.com/docs/plugin/advanced-flat-rate-shipping-method-for-woocommerce' ) ;
?>"><?php 
esc_html_e( 'Plugin documentation', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
                </li>
                <li>
                    <img src="<?php 
echo  esc_url( $image_url ) ;
?>">
                    <a target="_blank"
                       href="<?php 
echo  esc_url( 'www.thedotstore.com/support' ) ;
?>"><?php 
esc_html_e( 'Support platform', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
                </li>
                <li>
                    <img src="<?php 
echo  esc_url( $image_url ) ;
?>">
                    <a target="_blank"
                       href="<?php 
echo  esc_url( 'www.thedotstore.com/suggest-a-feature' ) ;
?>"><?php 
esc_html_e( 'Suggest A Feature', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
                </li>
                <li>
                    <img src="<?php 
echo  esc_url( $image_url ) ;
?>">
                    <a target="_blank"
                       href="<?php 
echo  esc_url( 'http://www.thedotstore.com/advanced-flat-rate-shipping-method-for-woocommerce#tab-change-log' ) ;
?>"><?php 
esc_html_e( 'Changelog', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
                </li>
            </ul>
        </div>
    </div>
<!-- html for popular plugin !-->

<div class="dotstore-important-link">
        <h2>
            <span class="dotstore-important-link-title">
                <?php 
esc_html_e( 'Our Popular plugins', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
            </span>
        </h2>
        <div class="video-detail important-link">
            <ul>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Conditional-Product-Fees-For-WooCommerce-Checkout.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Conditional Product Fees For WooCommerce Checkout', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-conditional-product-fees-checkout/" ) ;
?>">
						<?php 
esc_html_e( 'Extra Fees Plugin for WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/plugn-login-128.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Hide Shipping Method For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/hide-shipping-method-for-woocommerce/" ) ;
?>">
						<?php 
esc_html_e( 'Hide Shipping Method For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/WooCommerce Conditional Discount Rules For Checkout.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Conditional Discount Rules For WooCommerce Checkout', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-conditional-discount-rules-for-checkout/" ) ;
?>">
						<?php 
esc_html_e( 'Conditional Discount Rules For WooCommerce Checkout', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/WooCommerce-Blocker-Prevent-Fake-Orders.png' ) ;
?>" alt="<?php 
esc_attr_e( 'WooCommerce Blocker – Prevent Fake Orders', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/product/woocommerce-blocker-lite-prevent-fake-orders-blacklist-fraud-customers/" ) ;
?>">
						<?php 
esc_html_e( 'WooCommerce Anti-Fraud', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Advanced-Product-Size-Charts-for-WooCommerce.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Product Size Charts Plugin For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-advanced-product-size-charts/" ) ;
?>">
						<?php 
esc_html_e( 'Product Size Charts Plugin For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/wcbm-logo.png' ) ;
?>" alt="<?php 
esc_attr_e( 'WooCommerce Category Banner Management', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/product/woocommerce-category-banner-management/" ) ;
?>">
						<?php 
esc_html_e( 'WooCommerce Category Banner Management', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/woo-product-att-logo.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Product Attachment For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-product-attachment/" ) ;
?>">
						<?php 
esc_html_e( 'Product Attachment For WooCommerce', 'advanced-flat-rate-shipping-for-woocommerce' );
?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="view-button">
            <a class="view_button_dotstore" href="<?php 
echo  esc_url( "http://www.thedotstore.com/plugins/" ) ;
?>"  target="_blank"><?php 
esc_html_e( 'View All', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
        </div>
    </div>

</div>
</div>
</div>