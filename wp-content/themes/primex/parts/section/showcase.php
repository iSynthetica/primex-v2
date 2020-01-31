<?php
/**
 * @var $content
 */

// var_dump($content);

if (empty($content)) {
    return;
}

if (!empty($content['slider'])) {
    snth_show_template('section/' . $content['showcase_style'] . '.php', array('content' => $content));
}
