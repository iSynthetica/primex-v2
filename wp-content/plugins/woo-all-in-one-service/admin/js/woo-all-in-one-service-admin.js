(function( $ ) {
	'use strict';

	$(document.body).on('click', "#repair_edit_submit", function(e) {
		var btn = $(this);
		var formData = $("#repair_edit_form").serializeArray();

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wooaioservice_edit',
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
						window.location.reload();
					} else  {
						alert(decoded.message);
					}
				} else {
					alert('Something went wrong');
				}
			}
		});
	});

})( jQuery );
