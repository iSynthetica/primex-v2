<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 09.09.18
 * Time: 13:26
 */
?>

<?php
if(
    !empty($content['section_title']) ||
    ( !empty($content['section_link']['label']) && !empty($content['section_link']['url']) )
) {
    $is_link = !empty($content['section_link']['label']) && !empty($content['section_link']['url']);

    $aling_class = $is_link ? 'align-left' : 'align-center';
    ?>
    <h2 class="section-title font-alt <?php echo $aling_class ?> mt-0 mb-50 mb-sm-30">
        <?php if (!empty($content['section_title'])) {
            echo $content['section_title'];
        } ?>

        <?php if( $is_link ) {
            ?>
            <a
                href="<?php echo $content['section_link']['url'] ?>"
                class="section-more right"
            >
                <?php echo $content['section_link']['label'] ?> <i class="fa fa-angle-right"></i>
            </a>
            <?php
        } ?>

    </h2>
    <?php
}
?>
