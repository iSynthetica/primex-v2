<h1>Import Users</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';

global $wpdb;

$terms = array();

$terms_ser = serialize($terms);

?>
<textarea id="" rows="10" style="width: 100%;max-width: 1000px;"><?php echo $terms_ser; ?></textarea>
<?php

