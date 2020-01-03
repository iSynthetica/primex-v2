(function( $ ) {
	'use strict';

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
					if (decoded.message) {
						alert(decoded.message);
					} else if (decoded.fragments) {
						updateFragments(decoded.fragments)
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
