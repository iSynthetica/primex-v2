(function( $ ) {
	'use strict';
	var actionContainer = $('#wooaio-discount-create-action');
	var formContainer = $('#wooaio-discount-create-container');

	$(document.body).on('click', "#open-create-discount-rule", function(e) {
		actionContainer.removeClass('wooaio-discount-create-closed').removeClass('wooaio-discount-create-opened').addClass('wooaio-discount-create-opened');
		formContainer.removeClass('wooaio-discount-create-closed').removeClass('wooaio-discount-create-opened').addClass('wooaio-discount-create-opened');
	});

	$(document.body).on('click', "#close-create-discount-rule", function(e) {
		actionContainer.removeClass('wooaio-discount-create-closed').removeClass('wooaio-discount-create-opened').addClass('wooaio-discount-create-closed');
		formContainer.removeClass('wooaio-discount-create-closed').removeClass('wooaio-discount-create-opened').addClass('wooaio-discount-create-closed');
	});

	$(document.body).on('click', "#create-discount-submit", function(e) {
		var formData = $("#wooaio-discount-create-form").serializeArray();
		var data = {
			formData: formData,
			action: 'wooaiodiscount_create_product_discount_rule'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', ".delete-discount-rule", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var single = btn.data('single');
		var data = {
			id: id,
			single: single,
			action: 'wooaiodiscount_delete_product_discount_rule'
		};

		var sureMessage = btn.data('confirm');
		var sure = confirm(sureMessage);

		if (sure) {
			ajaxRequest(data);
		}
	});

	$(document).ready(function() {});

	$(window).on('load', function () {});

	$(window).on('scroll', function() {});

	$(window).on('resize', function(e) {});

	function ajaxRequest(data, cb, cbError) {
		$.ajax({
			type: 'post',
			url: ajaxurl,
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
							cb();
						}
					} else {
						if (typeof cbError === 'function') {
							cbError();
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

	function updateFragments ( fragments ) {
		if ( fragments ) {
			$.each( fragments, function( key ) {
				$( key )
					.addClass( 'updating' )
					.fadeTo( '400', '0.6' )
					.block({
						message: null,
						overlayCSS: {
							opacity: 0.6
						}
					});
			});

			$.each( fragments, function( key, value ) {
				$( key ).replaceWith( value );
				$( key ).stop( true ).css( 'opacity', '1' ).unblock();
			});
		}
	}
})( jQuery );
