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

    $(document).ready(function() {});

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});
}(jQuery));
