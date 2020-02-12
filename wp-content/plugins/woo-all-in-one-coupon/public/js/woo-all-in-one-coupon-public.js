(function( $ ) {
	'use strict';

	$(document.body).on('click', ".wooaiocoupon_submit", function(e) {
		var btn = $(this);
		var form = btn.parents('.wooaioservice_form');
		var formData = form.serializeArray();
		var messageHolder = form.find('.wooaiocoupon_messages');

		var data = {
			'action': 'wooaiocoupon_submit',
			'formData': formData
		};

		ajaxRequest(data, function(decoded) {
			messageHolder.html(decoded.messageHtml);
		}, function(decoded) {
			messageHolder.html(decoded.messageHtml);
		});
	});

	$(document).ready(function() {});

	$(window).on('load', function () {});

	$(window).on('scroll', function() {});

	$(window).on('resize', function(e) {});

	function ajaxRequest(data, cb, cbError) {
		$.ajax({
			type: 'post',
			url: wooaiocouponJsObj.ajaxurl,
			data: data,
			success: function (response) {
				var decoded;

				try {
					decoded = $.parseJSON(response);
				} catch(err) {
					console.log(err);
					decoded = false;
				}

				if (decoded) {
					if (decoded.message) {
						alert(decoded.message);
					}

					if (decoded.fragments) {
						updateFragments ( decoded.fragments );
					}

					if (decoded.success) {
						if (typeof cb === 'function') {
							cb(decoded);
						}
					} else {
						if (typeof cbError === 'function') {
							cbError(decoded);
						}
					}

					setTimeout(function () {
						if (decoded.url) {
							window.location.replace(decoded.url);
						} else if (decoded.reload) {
							window.location.reload();
						}
					}, 100);
				} else {
					alert('Something went wrong');
				}
			}
		});
	}

})( jQuery );
