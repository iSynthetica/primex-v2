<?php
/**
 * @var $content
 */

// var_dump($content);

if (empty($content['tabs']) || !array($content['tabs'])) {
    return;
}

$tabs = $content['tabs'];
?>

<div class="tabs clearfix nobottommargin" id="tab-1">

    <ul class="tab-nav clearfix">
        <?php
        foreach ($tabs as $tab) {
            ?><li><a href="#<?php echo $tab['tab_id'] ?>"><?php echo $tab['tab_title'] ?></a></li><?php
        }
        ?>
    </ul>

    <div class="tab-container">
        <?php
        foreach ($tabs as $tab) {
            $template = get_post($tab['tab_content']);
            if (!empty($template) && 'publish' === $template->post_status) {
                $template_settings = get_field('settings', $template->ID);
                ?>
                <div class="tab-content clearfix" id="<?php echo $tab['tab_id'] ?>">
                    <?php snth_show_section_content($template_settings); ?>
                </div>
                <?php
            }
        }
        ?>
    </div>

</div>
