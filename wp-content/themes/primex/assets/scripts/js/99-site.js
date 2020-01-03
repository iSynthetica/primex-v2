(function ($) {
    var productImageOwlCarousel;
    var productThumbOwlCarousel;
    var datepicker = $( "input[date='date']" );
    $(document.body).on('click', '.product-modal-desc-open', function() {
        var parentQuickView = $(this).parents('.product-quick-view');
        var parentQuickViewContent = parentQuickView.find('.modal-content');
        var parentQuickViewContentHtml = parentQuickViewContent.html();
        var productModalQuickView = $('#product-modal-desc');
        var productModalQuickViewContent = productModalQuickView.find('.modal-content');
        productModalQuickViewContent.html(parentQuickViewContentHtml);
        $('#product-modal-desc').modal();
    });


    $(document.body).on('click', "#top-cart-trigger", function(e) {
        $('#page-menu').toggleClass('pagemenu-active', false);
        $('#top-cart').toggleClass('top-cart-open');
        e.stopPropagation();
        e.preventDefault();

    });

    $(document.body).on('click', '#woocommerce-product-gallery__thumbnails a', function(e) {
        var sliderIndex = $(this).data('index');
        productImageOwlCarousel.trigger('to.owl.carousel', [sliderIndex]);
        e.stopPropagation();
        e.preventDefault();

    });

    $(document).ready(function() {
        productImageOwlCarousel = $('#woocommerce-product-gallery__images');

        productImageOwlCarousel.owlCarousel({
            margin: 0,
            items: 1,
            nav: 0,
            navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
            dots: 0
        });

        productThumbOwlCarousel = $('#woocommerce-product-gallery__thumbnails');

        productThumbOwlCarousel.owlCarousel({
            margin: 5,
            items: 1,
            nav: 1,
            navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
            dots: 0,
            responsive:{
                0:{ items:2 },
                576:{ items:2 },
                768:{ items:3 },
                992:{ items:3 },
                1200:{ items:3 }
            }
        });

        if (datepicker.length) {
            datepicker.each(function() {
                $(this).datepicker({
                    format: 'dd-mm-yyyy'
                });
            });
        }
    });

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});
}(jQuery));
