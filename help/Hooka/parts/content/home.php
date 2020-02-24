<?php
/**
 * Single Page Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) exit;


$sections = get_field('sections');

if(empty($sections)) {
    return;
}

foreach ($sections as $section) {
    snth_show_template('sections/home-'.$section['section_type'].'.php', array('content' => $section));
}
?>
