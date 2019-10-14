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
