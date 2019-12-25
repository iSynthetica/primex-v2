(function ($) {
    $(document.body).on('click', '#import-products-button', function (){
        var btn = $(this);
        var products = $('#import-products-field').val();

        var data = {
            action: 'wooaioie_import_products',
            products: products
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });
    });
})(jQuery);