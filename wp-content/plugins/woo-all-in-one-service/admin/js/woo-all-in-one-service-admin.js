(function( $ ) {
	'use strict';
	var actionContainer = $('#wooaioservice-access-create-action');
	var formContainer = $('#wooaioservice-access-create');

	$(document.body).on('click', "#open-create-access-rule", function(e) {
		actionContainer.removeClass('wooaioservice-access-create-closed').removeClass('wooaioservice-access-create-opened').addClass('wooaioservice-access-create-opened');
		formContainer.removeClass('wooaioservice-access-create-closed').removeClass('wooaioservice-access-create-opened').addClass('wooaioservice-access-create-opened');
	});

	$(document.body).on('click', "#close-create-access-rule", function(e) {
		actionContainer.removeClass('wooaioservice-access-create-closed').removeClass('wooaioservice-access-create-opened').addClass('wooaioservice-access-create-closed');
		formContainer.removeClass('wooaioservice-access-create-closed').removeClass('wooaioservice-access-create-opened').addClass('wooaioservice-access-create-closed');
	});

	$(document.body).on('click', ".access-delete-rule", function(e) {
		var btn = $(this);
		var role = btn.data('role');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wooaioservice_delete_access_rule',
				role: role
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

	$(document.body).on('click', ".access-update-rule", function(e) {
		var btn = $(this);
		var role = btn.data('role');
		var formData = $("#wooaioservice-access-create-form-" + role).serializeArray();

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wooaioservice_edit_access_rule',
				formData: formData,
				role: role
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

	$(document.body).on('click', "#create-access-rule", function(e) {
		var formData = $("#wooaioservice-access-create-form").serializeArray();

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wooaioservice_create_access_rule',
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
