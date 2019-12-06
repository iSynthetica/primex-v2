<style>
    .wp-admin .wrap {}
    .wp-admin .wrap.wprshrtcd-help .code-example {
        position: relative;
        background-color: rgba(0,0,0,.07);
        border: 1px solid rgba(0,0,0,.2);
        padding: 15px;
        margin-top: 25px;
    }
    .wp-admin .wrap.wprshrtcd-help .code-example .code-example-value {
        font-family: Consolas,Monaco,monospace;
    }
    .wp-admin .wrap.wprshrtcd-help .code-example .code-example-value.copied {
        background-color: rgba(0, 185, 238, 0.2);
    }
    .wp-admin .wrap.wprshrtcd-help .code-example .code-example-copy,
    .wp-admin .wrap.wprshrtcd-help .code-example .code-example-copied {
        display: inline-block;
        font-size: 12px;
        top: -20px;
        right: 10px;
        position: absolute;
        line-height: 1;
        color: #666;
        cursor: pointer;
    }
    .wp-admin .wrap.wprshrtcd-help .code-example .code-example-copy:hover {
        color: #333;
    }
    .wp-admin .wrap.wprshrtcd-help .code-example .code-example-copied {
        display: none;
        cursor: default;
    }

    @media screen and (min-width: 783px) {
        .wp-admin .wrap.wprshrtcd-help .row:after {
            display: table;
            content: ' ';
            clear: both;
        }
        .wp-admin .wrap.wprshrtcd-help .row .column {
            width: 48%;
            float: left;
        }
        .wp-admin .wrap.wprshrtcd-help .row .column:last-child {
            float: right;
        }
    }
</style>
<div class="wrap wprshrtcd-help">
    <h1><?php echo __('Woo Product Reviews Shortcode Help', 'woo-product-reviews-shrtcd'); ?></h1>

    <div class="row">
        <div class="column">
            <h3><?php echo __('Default shortcode usage', 'woo-product-reviews-shrtcd'); ?></h3>

            <p>
                <?php echo __('To input your user reviews list on any site page use shortcode ', 'woo-product-reviews-shrtcd'); ?>
                <code>[wprshrtcd_woo_product_reviews]</code>. <?php echo __('The only required parameter is <code>products_ids</code>', 'woo-product-reviews-shrtcd'); ?>
            </p>

            <p>
                <strong><?php echo __('Example', 'woo-product-reviews-shrtcd'); ?></strong>
            </p>

            <p class="code-example">
                <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123"]</span>

                <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
                <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
            </p>
        </div>

        <div class="column">
            <h3><?php echo __('Multiple products shortcode usage', 'woo-product-reviews-shrtcd'); ?></h3>

            <p>
                <?php echo __('You can use as many products as you want, just separate products IDs with comma. As additional parameter you can add <code>product_title</code> to use it for all products', 'woo-product-reviews-shrtcd'); ?>
                <?php echo __('Rating of multiple products will be summed up and shown as total rating.', 'woo-product-reviews-shrtcd'); ?>
            </p>

            <p>
                <strong><?php echo __('Example', 'woo-product-reviews-shrtcd'); ?></strong>
            </p>

            <p class="code-example">
                <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123,124,125" product_title="Multiple product title"]</span>

                <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
                <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
            </p>

            <p>
                <?php echo __('If you need to show separated amount of reviews rating add each product via single product short code.', 'woo-product-reviews-shrtcd'); ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <h3><?php echo __('Number of reviews shortcode usage', 'woo-product-reviews-shrtcd'); ?></h3>

            <p>
                <?php echo __('You can set up how many reviews should be displayed, by default it’s 5. Use parameter <code>per_page</code> to change this value. If you want to show all reviews set it to <strong>all</strong>', 'woo-product-reviews-shrtcd'); ?>
            </p>

            <p>
                <strong><?php echo __('Example', 'woo-product-reviews-shrtcd'); ?></strong>
            </p>

            <p class="code-example">
                <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123" per_page="all"]</span>

                <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
                <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
            </p>
        </div>

        <div class="column">
            <h3><?php echo __('No reviews just aggregate rating usage', 'woo-product-reviews-shrtcd'); ?></h3>

            <p>
                <?php echo __('You can hide reviews list and show only aggregate rating as average rating from all customers reviews. Use parameter <code>per_page</code> and set it to <strong>0</strong>', 'woo-product-reviews-shrtcd'); ?>
            </p>

            <p>
                <strong><?php echo __('Example', 'woo-product-reviews-shrtcd'); ?></strong>
            </p>

            <p class="code-example">
                <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123" per_page="0"]</span>

                <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
                <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <h3><?php echo __('Show reviews reply shortcode usage', 'woo-product-reviews-shrtcd'); ?></h3>

            <p>
                <?php echo __('By default only customer reviews will be displayed, if you want to show reviews replies use parameter <code>show_nested</code> with value <strong>yes</strong>', 'woo-product-reviews-shrtcd'); ?>
            </p>

            <p>
                <strong><?php echo __('Example', 'woo-product-reviews-shrtcd'); ?></strong>
            </p>

            <p class="code-example">
                <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123" show_nested="yes"]</span>

                <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
                <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
            </p>
        </div>

        <div class="column">
            <h3><?php echo __('Disabled Structured Data shortcode usage', 'woo-product-reviews-shrtcd'); ?></h3>

            <p>
                <?php echo __('If you are going to use more then one shortcode per page we recommend you to use structured data markup only for one, otherwise google get two reviews schemas. You can disable it by adding parameter <code>show_schema</code> with value <strong>no</strong>', 'woo-product-reviews-shrtcd'); ?>
            </p>

            <p>
                <strong><?php echo __('Example', 'woo-product-reviews-shrtcd'); ?></strong>
            </p>

            <p class="code-example">
                <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123" show_schema="no"]</span>

                <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
                <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
            </p>
        </div>
    </div>

    <h3><?php echo __('All parameters usage example', 'woo-product-reviews-shrtcd'); ?></h3>

    <p>
        <?php echo __('Show multiple products reviews in one shortcode: ', 'woo-product-reviews-shrtcd'); ?> <code>products_ids="111,112,123"</code><br>
    </p>

    <p>
        <?php echo __('Set one title for multiple products: ', 'woo-product-reviews-shrtcd'); ?> <code>product_title="Multiple product title"</code><br>
    </p>

    <p>
        <?php echo __('Show all product reviews: ', 'woo-product-reviews-shrtcd'); ?> <code>per_page="all"</code><br>
    </p>

    <p>
        <?php echo __('Show nested reviews (reply, comments etc.): ', 'woo-product-reviews-shrtcd'); ?> <code>show_nested="yes"</code><br>
    </p>

    <p>
        <?php echo __('Do not show schema.org markup: ', 'woo-product-reviews-shrtcd'); ?> <code>show_schema="no"</code><br>
    </p>

    <p class="code-example">
        <span class="code-example-value">[wprshrtcd_woo_product_reviews products_ids="123,124,125" product_title="Multiple product title" per_page="all" show_nested="yes" show_schema="no"]</span>

        <span class="code-example-copy"><?php echo __('Copy', 'woo-product-reviews-shrtcd'); ?></span>
        <span class="code-example-copied"><?php echo __('Copied. Input into your content!', 'woo-product-reviews-shrtcd'); ?></span>
    </p>

    <h3><?php echo __('Testing', 'woo-product-reviews-shrtcd'); ?></h3>

    <p>
        <?php echo __('Test your Schema.org by check your page with the <a href="https://search.google.com/structured-data/testing-tool" target="_blank">google structured data tool</a>', 'woo-product-reviews-shrtcd'); ?>
    </p>

    <h3><?php echo __('Reviews shortcode templating', 'woo-product-reviews-shrtcd'); ?></h3>

    <p>
        <?php echo __('Styles are taken from WooCommerce, so it looks the same as on the product details page.', 'woo-product-reviews-shrtcd'); ?>
        <?php echo __('Structure of the reviews can be changed by copy files in the list below to your custom field, then this structure will be used by default', 'woo-product-reviews-shrtcd'); ?>:
    </p>

    <ul>
        <li>
            <code>woo-product-reviews-shrtcd/templates/product-reviews.php</code>
            =>
            <code>[YOUR_THEME]/woocommerce/wprshrtcd/product-reviews.php</code>
        </li>

        <li>
            <code>woo-product-reviews-shrtcd/templates/review/review.php</code>
            =>
            <code>[YOUR_THEME]/woocommerce/wprshrtcd/review/review.php</code>
        </li>

        <li>
            <code>woo-product-reviews-shrtcd/templates/review/review-meta.php</code>
            =>
            <code>[YOUR_THEME]/woocommerce/wprshrtcd/review/review-meta.php</code>
        </li>

        <li>
            <code>woo-product-reviews-shrtcd/templates/review/review-rating.php</code>
            =>
            <code>[YOUR_THEME]/woocommerce/wprshrtcd/review/review-rating.php</code>
        </li>
    </ul>

    <h3><?php echo __('More great plugins', 'woo-product-reviews-shrtcd'); ?></h3>

    <p>
        <?php echo __('Please have a look at our other plugins too by visiting <a href="https://saleswonder.biz/" target="_blank">saleswonder.biz</a>', 'woo-product-reviews-shrtcd'); ?>
    </p>
</div>

<script>
    (function( $ ) {
        $(document.body).on('click', '.code-example-copy', function() {
            var control = $(this);
            var parent = control.parents('.code-example');
            var message = parent.find('.code-example-copied');
            var toCopy = parent.find('.code-example-value');

            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(toCopy.text()).select();
            document.execCommand("copy");
            $temp.remove();

            toCopy.addClass('copied');
            control.hide();
            message.show();

            setTimeout(function() {
                toCopy.removeClass('copied');
                control.show();
                message.hide();
            }, 2000);
        });
    })( jQuery );
</script>