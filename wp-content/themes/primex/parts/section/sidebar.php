<?php
/**
 * @var $content
 */

// var_dump($content);

if (empty($content)) {
    return;
}
?>

<div class="row clearfix">
    <div class="col-md-8 col-lg-9 order-first order-md-last">
        <?php
        if (!empty($content['content_title'])) {
            ?><h4><?php echo $content['content_title']; ?></h4><?php
        }

        if (!empty($content['main_content'])) {
            $template = get_post($content['main_content']);

            if (!empty($template) && 'publish' === $template->post_status) {
                $template_settings = get_field('settings', $template->ID);
                snth_show_section_content( $template_settings );
            }
        }
        ?>
    </div>

    <div class="col-md-4 col-lg-3 order-last order-md-first">
        <?php
        if (!empty($content['sidebar_title'])) {
            ?><h4 class="center"><?php echo $content['sidebar_title']; ?></h4><?php
        }

        if (!empty($content['sidebar_content'])) {
            $template = get_post($content['sidebar_content']);

            if (!empty($template) && 'publish' === $template->post_status) {
                $template_settings = get_field('settings', $template->ID);
                snth_show_section_content( $template_settings );
            }
        }
        ?>
    </div>
</div>
