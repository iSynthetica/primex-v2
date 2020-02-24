<?php
/**
 * Page Default sidebar
 *
 * @package Hookah/Parts/Sidebar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- Search Widget -->
<div class="widget">
    <form class="form-inline form" role="form">
        <div class="search-wrap">
            <button class="search-button animate" type="submit" title="Start Search">
                <i class="fa fa-search"></i>
            </button>
            <input type="text" class="form-control search-field" placeholder="Search...">
        </div>
    </form>
</div>
<!-- End Search Widget -->

<!-- Widget -->
<div class="widget">

    <h5 class="widget-title font-alt">Categories</h5>

    <div class="widget-body">
        <ul class="clearlist widget-menu">
            <li>
                <a href="#" title="">Branding</a>
                <small>
                    - 7
                </small>
            </li>
            <li>
                <a href="#" title="">Design</a>
                <small>
                    - 15
                </small>
            </li>
            <li>
                <a href="#" title="">Development</a>
                <small>
                    - 3
                </small>
            </li>
            <li>
                <a href="#" title="">Photography</a>
                <small>
                    - 5
                </small>
            </li>
            <li>
                <a href="#" title="">Other</a>
                <small>
                    - 1
                </small>
            </li>
        </ul>
    </div>

</div>
<!-- End Widget -->

<!-- Widget -->
<div class="widget">

    <h5 class="widget-title font-alt">Tags</h5>

    <div class="widget-body">
        <div class="tags">
            <a href="">Design</a>
            <a href="">Portfolio</a>
            <a href="">Digital</a>
            <a href="">Branding</a>
            <a href="">Theme</a>
            <a href="">Clean</a>
            <a href="">UI & UX</a>
            <a href="">Love</a>
        </div>
    </div>

</div>
<!-- End Widget -->

<!-- Widget -->
<div class="widget">

    <h5 class="widget-title font-alt">Last posts</h5>

    <div class="widget-body">
        <ul class="clearlist widget-posts">
            <li class="clearfix">
                <a href=""><img src="<?php echo SNTH_IMAGES_URL; ?>/blog/previews/post-prev-1.jpg" alt="" class="widget-posts-img"/></a>
                <div class="widget-posts-descr">
                    <a href="#" title="">Minimalistic Design Forever</a>
                    Posted by John Doe 7 Mar
                </div>
            </li>
            <li class="clearfix">
                <a href=""><img src="<?php echo SNTH_IMAGES_URL; ?>/blog/previews/post-prev-2.jpg" alt="" class="widget-posts-img"/></a>
                <div class="widget-posts-descr">
                    <a href="#" title="">Ipsum dolor sit amet, consectetur adipiscing elit</a>
                    Posted by John Doe 7 Mar
                </div>
            </li>
            <li class="clearfix">
                <a href=""><img src="<?php echo SNTH_IMAGES_URL; ?>/blog/previews/post-prev-3.jpg" alt="" class="widget-posts-img"/></a>
                <div class="widget-posts-descr">
                    <a href="#" title="">New Web Design Trends in 2015 year</a>
                    Posted by John Doe 7 Mar
                </div>
            </li>
            <li class="clearfix">
                <a href=""><img src="<?php echo SNTH_IMAGES_URL; ?>/blog/previews/post-prev-4.jpg" alt="" class="widget-posts-img"/></a>
                <div class="widget-posts-descr">
                    <a href="#" title="">Hipster’s Style in Web Design and Logo</a>
                    Posted by John Doe 7 Mar
                </div>
            </li>
            <li class="clearfix">
                <a href=""><img src="<?php echo SNTH_IMAGES_URL; ?>/blog/previews/post-prev-5.jpg" alt="" class="widget-posts-img"/></a>
                <div class="widget-posts-descr">
                    <a href="#" title="">Duis tristique condimentum nulla, bibendum consectetu</a>
                    Posted by John Doe 7 Mar
                </div>
            </li>
        </ul>
    </div>

</div>
<!-- End Widget -->

<!-- Widget -->
<div class="widget">

    <h5 class="widget-title font-alt">Comments</h5>

    <div class="widget-body">
        <ul class="clearlist widget-comments">
            <li>
                John Doe on <a href="#" title="">Lorem ipsum dolor sit amet</a>
            </li>
            <li>
                Emma Johnson on <a href="#" title="">Suspendisse accumsan interdum tellus</a>
            </li>
            <li>
                John Doe on <a href="#" title="">Praesent ultricies ut ipsum</a>
            </li>
            <li>
                Mark Deen on <a href="#" title="">Vivamus malesuada vel nulla vulputate</a>
            </li>
            <li>
                Sam Brin on <a href="#" title="">Aenean auctor egestas auctor</a>
            </li>
        </ul>
    </div>

</div>
<!-- End Widget -->

<!-- Widget -->
<div class="widget">

    <h5 class="widget-title font-alt">Text widget</h5>

    <div class="widget-body">
        <div class="widget-text clearfix">

            <img src="<?php echo SNTH_IMAGES_URL; ?>/user-avatar.png" alt="" width="70" class="img-circle left img-left">

            Consectetur adipiscing elit. Quisque magna ante eleifend eleifend.
            Purus ut dignissim consectetur, nulla erat ultrices purus, ut consequat sem elit non sem.
            Quisque magna ante eleifend eleifend.

        </div>
    </div>

</div>
<!-- End Widget -->

<!-- Widget -->
<div class="widget">

    <h5 class="widget-title font-alt">Archive</h5>

    <div class="widget-body">
        <ul class="clearlist widget-menu">
            <li>
                <a href="#" title="">February 2014</a>
            </li>
            <li>
                <a href="#" title="">January 2014</a>
            </li>
            <li>
                <a href="#" title="">December 2013</a>
            </li>
        </ul>
    </div>

</div>
<!-- End Widget -->
