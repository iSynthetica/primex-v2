(function( $ ) {
	'use strict';


	var actionContainer = $('#wooaio-currency-create-action');
	var formContainer = $('#wooaio-currency-create-container');
	var editedHtml = '';

	$(document.body).on('click', "#open-create-currency-rule", function(e) {
		actionContainer.removeClass('wooaio-currency-create-closed').removeClass('wooaio-currency-create-opened').addClass('wooaio-currency-create-opened');
		formContainer.removeClass('wooaio-currency-create-closed').removeClass('wooaio-currency-create-opened').addClass('wooaio-currency-create-opened');
	});

	$(document.body).on('click', "#close-create-currency-rule", function(e) {
		actionContainer.removeClass('wooaio-currency-create-closed').removeClass('wooaio-currency-create-opened').addClass('wooaio-currency-create-closed');
		formContainer.removeClass('wooaio-currency-create-closed').removeClass('wooaio-currency-create-opened').addClass('wooaio-currency-create-closed');
	});

	$(document.body).on('click', "#add-currency-submit", function(e) {
		var formData = $("#wooaio-currency-create-form").serializeArray();
		var data = {
			formData: formData,
			action: 'wooaiocurrency_add_currency_rule'
		};

		ajaxRequest(data);
	});

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
