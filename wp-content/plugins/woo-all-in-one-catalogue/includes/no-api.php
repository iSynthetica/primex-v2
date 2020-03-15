<?php
function wooaioc_api_get_content() {
    ob_start();
    ?>
    <h1>No API</h1>
    <?php
    return ob_get_clean();
}