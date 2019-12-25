<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wooaioie_Background_Create_Product {
    protected static $bg_process;

    public static function init() {
        include_once WOOAIOIE_PATH . 'includes/class-logger.php';
        include_once WOOAIOIE_PATH . 'includes/abstract-class-background.php';
        include_once WOOAIOIE_PATH . 'includes/class-background-create-products-process.php';

        self::$bg_process = new Wooaioie_Background_Create_Product_Process();
    }

    public static function bg_process($products) {
        $i = 0;
        foreach($products as $product) {
            self::$bg_process->push_to_queue( $product );

            $i++;
        }

        self::$bg_process->save()->dispatch();

        return $i;
    }
}

add_action( 'init', array( 'Wooaioie_Background_Create_Product', 'init' ) );