(function( $ ) {
	'use strict';

	$(document.body).on('click', "#wooaioservice_create", function(e) {
		var actionContainer = $('#wooaioservice_form_container_control');
		var formContainer = $('#wooaioservice_form_container');

		actionContainer.removeClass('wooaioservice-form-closed').removeClass('wooaioservice-form-opened').addClass('wooaioservice-form-opened');
		formContainer.removeClass('wooaioservice-form-closed').removeClass('wooaioservice-form-opened').addClass('wooaioservice-form-opened');
	});

	$(document.body).on('click', "#wooaioservice_cancel", function(e) {
		var actionContainer = $('#wooaioservice_form_container_control');
		var formContainer = $('#wooaioservice_form_container');

		actionContainer.removeClass('wooaioservice-form-closed').removeClass('wooaioservice-form-opened').addClass('wooaioservice-form-closed');
		formContainer.removeClass('wooaioservice-form-closed').removeClass('wooaioservice-form-opened').addClass('wooaioservice-form-closed');
	});

	$(document.body).on('click', "#wooaioservice_submit", function(e) {
		var btn = $(this);
		var formData = $("#wooaioservice_form").serializeArray();

		$.ajax({
			url: wooaioserviceJsObj.ajaxurl,
			method: 'POST',
			data: {
				action: 'wooaioservice_submit',
				formData: formData
			},
			success: function(response) {
				var decoded;

				try {
					decoded = $.parseJSON(response);
				} catch(err) {
					console.log(err);
					decoded = false;
				}

				if (decoded) {
					if (decoded.fragments) {
						updateFragments(decoded.fragments)

						setTimeout(function() {
							if (decoded.success) {
								$(document.body).trigger( "wooaioservice:success" );
							} else {
								$(document.body).trigger( "wooaioservice:error" );
							}
						},500)
					} else if (decoded.message) {
						alert(decoded.message);
					}

					if (decoded.scrollToFragment) {
						var scrollPosition = $(decoded.scrollToFragment).offset().top - 80;
                        $('html, body').animate({
                            scrollTop: scrollPosition
                        }, 1000)
					}
				} else {
					alert('Something went wrong');
				}
			}
		});
	});

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

			$( document.body ).trigger( 'wc_fragments_loaded' );
		}
	};

	$(document).ready(function() {});

	$(window).on('load', function () {});

	$(window).on('scroll', function() {});

	$(window).on('resize', function(e) {});

})( jQuery );
