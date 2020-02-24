<?php
/**
 * Single Page Content
 *
 * @package Hooka/Parts/Sections/Home
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$section_content = $content['section_bestsellers'];

$ids = implode(', ', $section_content);

snth_before_home_section_content($content);

?>

<?php snth_woo_show_products_slider($section_content); ?>

<?php

snth_after_home_section_content();

?>
