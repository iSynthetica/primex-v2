<?php
$shortcodes = apply_filters( 'snth_shortcodes', array() );

foreach($shortcodes as $shortcode) {
    add_shortcode( $shortcode['id'], $shortcode['callback'] );
}

function snth_widget_recent_posts() {
    ob_start();
    ?>
    <div id="post-list-footer">
        <div class="spost clearfix">
            <div class="entry-c">
                <div class="entry-title">
                    <h4><a href="#">Lorem ipsum dolor sit amet, consectetur</a></h4>
                </div>
                <ul class="entry-meta">
                    <li>10th July 2014</li>
                </ul>
            </div>
        </div>

        <div class="spost clearfix">
            <div class="entry-c">
                <div class="entry-title">
                    <h4><a href="#">Elit Assumenda vel amet dolorum quasi</a></h4>
                </div>
                <ul class="entry-meta">
                    <li>10th July 2014</li>
                </ul>
            </div>
        </div>

        <div class="spost clearfix">
            <div class="entry-c">
                <div class="entry-title">
                    <h4><a href="#">Debitis nihil placeat, illum est nisi</a></h4>
                </div>
                <ul class="entry-meta">
                    <li>10th July 2014</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function snth_widget_blogroll() {
    ob_start();
    ?>
    <ul>
        <li><a href="http://codex.wordpress.org/">Documentation</a></li>
        <li><a href="http://wordpress.org/support/forum/requests-and-feedback">Feedback</a></li>
        <li><a href="http://wordpress.org/extend/plugins/">Plugins</a></li>
        <li><a href="http://wordpress.org/support/">Support Forums</a></li>
        <li><a href="http://wordpress.org/extend/themes/">Themes</a></li>
        <li><a href="http://wordpress.org/news/">WordPress Blog</a></li>
        <li><a href="http://planet.wordpress.org/">WordPress Planet</a></li>
    </ul>
    <?php
    return ob_get_clean();
}

function snth_cart_icon() {
    global $woocommerce;
    $cart_count = WC()->cart->cart_contents_count; // Set variable for cart item count
    $cart_url = wc_get_cart_url();  // Set Cart URL
    $cart = WC()->cart->get_cart();

    ob_start();
    ?>
    <div id="top-cart">
        <a href="#" id="top-cart-trigger"><i class="fas fa-shopping-cart"></i><span><?php echo $cart_count; ?></span></a>
        <div class="top-cart-content">
            <div class="top-cart-title">
                <h4><?php echo __( 'Shopping Cart', 'primex' ); ?></h4>
            </div>
            <div class="top-cart-items">
                <?php
                foreach( $cart as $cart_item ){
                    // var_dump($cart_item);
                    $product = wc_get_product( $cart_item['product_id'] );
                    ?>
                    <div class="top-cart-item clearfix">
                        <div class="top-cart-item-image">
                            <a href="<?php echo get_permalink( $product->get_id() ); ?>">
                                <img
                                        class="image_fade"
                                        src="<?php echo get_the_post_thumbnail_url( $product->get_id(), 'thumbnail' ); ?>"
                                        alt="<?php echo $product->get_name(); ?>"
                                >
                            </a>
                        </div>
                        <div class="top-cart-item-desc">
                            <a href="<?php echo get_permalink( $product->get_id() ); ?>"><?php echo $product->get_name(); ?></a>
                            <span class="top-cart-item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                            <span class="top-cart-item-quantity">x <?php echo $cart_item['quantity']; ?></span>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="top-cart-action clearfix">
                <span class="fleft top-checkout-price"><?php echo $woocommerce->cart->get_cart_total(); ?></span>
                <a href="<?php echo $cart_url; ?>" class="button button-3d button-small nomargin fright">
                    <?php echo __( 'View Cart', 'primex' ); ?>
                </a>
            </div>
        </div>
    </div><!-- #top-cart end -->
    <?php
    return ob_get_clean();
}

function snth_social() {
    $social = get_field('social', 'options');

    foreach ($social as $item) {
        ?>
        <a
            href="<?php echo $item['link'] ?>"
            class="social-icon si-small si-rounded topmargin-sm si-<?php echo $item['icon'] ?> display-on-<?php echo $item['use_on'] ?>"
            target="_blank"
            rel="nofollow"
        >
            <i class="icon-<?php echo $item['icon'] ?> fab fa-<?php echo $item['icon'] ?>"></i>
            <i class="icon-<?php echo $item['icon'] ?> fab fa-<?php echo $item['icon'] ?>"></i>
        </a>
        <?php
    }
}

function snth_phones_header() {
    $social = get_field('phones', 'options');

    ?>
    <ul class="header-extras header-phones">
    <?php
    foreach ($social as $item) {
        ?>
        <li>
            <div class="he-text">
                <?php echo $item['label'] ?>
            </div>
        </li>
        <?php
    }
    ?>
    </ul>
    <?php
}
