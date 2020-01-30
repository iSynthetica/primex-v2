<?php
/**
 * @var $allowed_tabs
 * @var $allowed_tabs_keys
 * @var $active_tab
 */
?>
<h2 id="wooaioservice-nav-tab" class="nav-tab-wrapper">
    <?php
    foreach ($allowed_tabs as $allowed_tab_key => $allowed_tab) {
        ?>
        <a href="?page=wooaiodiscount&tab=<?php echo $allowed_tab_key ?>"
           class="nav-tab <?php echo $active_tab == $allowed_tab_key ? 'nav-tab-active' : ''; ?>"
        ><?php echo $allowed_tab['title'] ?></a>
        <?php
    }
    ?>
</h2>
