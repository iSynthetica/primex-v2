(function( $ ) {
	'use strict';

	$(document.body).on('click', ".turbosms-settings-submit", function(e) {
		var btn = $(this);
		var form = btn.data('form');
		var setting = btn.data('setting');
		var formData = $("#" + form).serializeArray();

		var data = {
			formData: formData,
			setting: setting,
			action: 'wooaio_turbosms_settings_update'
		};

		ajaxRequest(data);
	});

	$(document.body).on('change', ".turbosms_send_enable", function(e) {
		var btn = $(this);
		var val = btn.val();
		var container_key = btn.data('container');
		var container = $('#turbosms_text_container_' + container_key);

		if ('yes' == val) {
			container.show();
		} else {
			container.hide();
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
