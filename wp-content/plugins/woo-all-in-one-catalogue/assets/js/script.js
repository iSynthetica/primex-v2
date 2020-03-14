(function ($) {
    $(document.body).on('click', ".catalogue-item-add-to-cart", function(e) {
        var btn = $(this);
        var productRow = btn.parents('tr');
        var quantity = productRow.find('.catalogue-item-qty').val();
        var product_id = btn.data('id');

        $.ajax({
            url: wooaiocJsObj.ajaxurl,
            method: 'POST',
            data: {
                action: 'wooaioc_add_to_cart',
                quantity: quantity,
                product_id: product_id
            },
            success: function (response) {
                if ( ! response ) {
                    return;
                }

                if ( response.error && response.product_url ) {
                    return;
                }

                $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, btn ] );
            },
            dataType: 'json'
        });
    });

    $(document).ready(function() {
        var catalogueLoadingContainer = $('.catalogue-loading-container');

        if (catalogueLoadingContainer.length) {

            $.ajax({
                url: wooaiocJsObj.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wooaioc_load_catalogue'
                },
                success: function (response) {
                    if ( ! response ) {
                        return;
                    }

                    if ( response.error ) {
                        return;
                    }

                    if (response.html) {
                        $('#catalogue-loading').remove();

                        catalogueLoadingContainer.replaceWith( response.html );
                    }
                },
                dataType: 'json'
            });
        }
    });

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});
}(jQuery));