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

	$(document).ready(function() {});

	$(window).on('load', function () {});

	$(window).on('scroll', function() {});

	$(window).on('resize', function(e) {});

})( jQuery );
