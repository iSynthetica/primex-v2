<?php
/**
 * @var $content
 */

//var_dump($content['image_1']);
//var_dump($content['image_2']);

if (empty($content['slider'] || !is_array($content['slider']))) {

}

foreach ($content['slider'] as $slide) {
    // var_dump($slide);
}
?>

<div class="row no-gutters showcase-section showcase-style-1">
    <div class="showcase-col-1 col-lg-8">
        <div class="showcase-big-1">
            <div class="showcase-inner">
                <div class="owl-carousel carousel-widget"
                     data-margin="0"
                     data-items="1"
                     data-loop="true"
                     data-nav="false"
                     data-autoplay="4000"
                     style="height: 100%;width: 100%;"
                >
                    <?php
                    foreach ($content['slider'] as $slide) {
                        $img_url = $slide['image']['url'];
                        $url = '';
                        if (!empty($slide['url']['url'])) {
                            $url = $slide['url']['url'];
                        }
                        ?>
                        <div class="showcase-carousel-item" style="background-image: url('<?php echo $img_url ?>');background-position: center;background-size: cover;height: 100%;width: 100%;">
                            <?php
                            if (!empty($url)) {
                                ?>
                                <a class="showcase-carousel-url" href="<?php echo $url ?>">
                                    <div class="showcase-carousel-content">
                                        <?php
                                        if (!empty($slide['title'])) {
                                            ?><h4><?php echo $slide['title'] ?></h4><?php
                                        }

                                        if (!empty($slide['text'])) {
                                            echo wpautop($slide['text']);
                                        }
                                        ?>
                                    </div>
                                </a>
                                <?php
                            } else {
                                ?>
                                <div class="showcase-carousel-url">
                                    <div class="showcase-carousel-content">
                                        <?php
                                        if (!empty($slide['title'])) {
                                            ?><h4><?php echo $slide['title'] ?></h4><?php
                                        }

                                        if (!empty($slide['text'])) {
                                            echo wpautop($slide['text']);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="showcase-col-2 col-lg-4">
        <div class="row no-gutters">
            <div class="col-12 col-sm-6 col-lg-12">
                <div class="showcase-small-item showcase-small-1">
                    <div
                        class="showcase-inner"
                        style="background-image: url('<?php echo $content['image_1']['image']['url'] ?>');background-position: center;background-size: cover;"
                    >
                    <?php

                    $url = '';
                    if (!empty($content['image_1']['url']['url'])) {
                        $url = $content['image_1']['url']['url'];
                    }

                    if (!empty($url)) {
                        ?>
                        <a class="showcase-small-url" href="<?php echo $url ?>">
                            <div class="showcase-small-content">
                                <?php
                                if (!empty($content['image_1']['title'])) {
                                    ?><h4><?php echo $content['image_1']['title'] ?></h4><?php
                                }
                                ?>
                            </div>
                        </a>
                        <?php
                    } else {
                        ?>
                        <div class="showcase-small-url">
                            <div class="showcase-small-content">
                                <?php
                                if (!empty($content['image_1']['title'])) {
                                    ?><h4><?php echo $content['image_1']['title'] ?></h4><?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-12">
                <div class="showcase-small-item showcase-small-2">
                    <div
                        class="showcase-inner"
                        style="background-image: url('<?php echo $content['image_2']['image']['url'] ?>');background-position: center;background-size: cover;"
                    >
                        <?php

                        $url = '';
                        if (!empty($content['image_2']['url']['url'])) {
                            $url = $content['image_2']['url']['url'];
                        }

                        if (!empty($url)) {
                            ?>
                            <a class="showcase-small-url" href="<?php echo $url ?>">
                                <div class="showcase-small-content">
                                    <?php
                                    if (!empty($content['image_2']['title'])) {
                                        ?><h4><?php echo $content['image_2']['title'] ?></h4><?php
                                    }
                                    ?>
                                </div>
                            </a>
                            <?php
                        } else {
                            ?>
                            <div class="showcase-small-url">
                                <div class="showcase-small-content">
                                    <?php
                                    if (!empty($content['image_2']['title'])) {
                                        ?><h4><?php echo $content['image_2']['title'] ?></h4><?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
