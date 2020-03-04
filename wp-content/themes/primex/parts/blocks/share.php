<?php
/**
 * @see https://www.addtoany.com/buttons/for/website
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (is_checkout() || is_cart()) {
    return;
}

?>
<!-- AddToAny BEGIN -->
<div class="mt-2 mb-2 text-center">
    <div class="a2a_kit a2a_kit_size_32 a2a_default_style text-center">
        <a class="a2a_button_viber"></a>
        <a class="a2a_button_telegram"></a>
        <a class="a2a_button_whatsapp"></a>
        <a class="a2a_button_facebook_messenger"></a>
        <a class="a2a_button_facebook"></a>
        <a class="a2a_button_twitter"></a>
        <a class="a2a_button_vk"></a>
        <!--    <a class="a2a_button_email"></a>-->
    </div>
    <script async src="https://static.addtoany.com/menu/page.js"></script>
</div>
<!-- AddToAny END -->