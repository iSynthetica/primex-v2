(function( $ ) {
	'use strict';

	$(document.body).on('click', ".wooaiocoupon_submit", function(e) {
		var btn = $(this);
		var form = btn.parents('.wooaioservice_form');
		var formData = form.serializeArray();
		var fieldsHolder = form.find('.wooaiocoupon_fields_holder');
		var descriptionHolder = form.find('.wooaiocoupon_description_holder');
		var messageHolder = form.find('.wooaiocoupon_messages');
		messageHolder.html('');
		var data = {'action': 'wooaiocoupon_submit', 'formData': formData};

		if (wooaiocouponJsObj.grcV3Key) {
			grecaptcha.execute(wooaiocouponJsObj.grcV3Key, {action: 'submit'}).then(function(token) {
				data.formData.push({name: 'recaptchaResponse', value: token});

				ajaxRequest(data, function(decoded) {
					btn.remove();
					fieldsHolder.remove();
					descriptionHolder.remove();
					messageHolder.html(decoded.messageHtml);
				}, function(decoded) {
					messageHolder.html(decoded.messageHtml);
				});
			});

		} else {
			ajaxRequest(data, function(decoded) {
				btn.remove();
				fieldsHolder.remove();
				descriptionHolder.remove();
				messageHolder.html(decoded.messageHtml);
			}, function(decoded) {
				messageHolder.html(decoded.messageHtml);
			});
		}


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
