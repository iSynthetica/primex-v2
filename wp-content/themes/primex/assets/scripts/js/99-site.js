(function ($) {
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

    $(document).ready(function() {});

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});
}(jQuery));
