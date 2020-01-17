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

	$(document.body).on('click', ".delete-currency-rule", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var data = {
			id: id,
			action: 'wooaiocurrency_delete_currency_rule'
		};

		var sureMessage = btn.data('confirm');
		var sure = confirm(sureMessage);

		if (sure) {
			ajaxRequest(data);
		}
	});

	$(document.body).on('click', ".make-base-currency-rule", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var data = {
			id: id,
			action: 'wooaiocurrency_make_base'
		};

		var sureMessage = btn.data('confirm');
		var sure = confirm(sureMessage);

		if (sure) {
			ajaxRequest(data);
		}
	});

	$(document.body).on('click', ".make-main-currency-rule", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var data = {
			id: id,
			action: 'wooaiocurrency_make_main'
		};

		var sureMessage = btn.data('confirm');
		var sure = confirm(sureMessage);

		if (sure) {
			ajaxRequest(data);
		}
	});

	$(document.body).on('click', "#add-currency-rate", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var index = $('.wooaio-currency-rate-item').length;
		var data = {
			id: id,
			index: index,
			action: 'wooaiocurrency_add_currency_rate'
		};

		ajaxRequest(data, function(decoded) {
			if (decoded.template) {
				var template = $(decoded.template);

				$('#currency_rate_set_action').hide();
				$('#currency_rate_set').append(template);
			}
		});
	});

	$(document.body).on('click', ".currency-rate-item-cancel", function(e) {
		var btn = $(this);
		var parent = btn.parents('.wooaio-currency-item');
		parent.remove();
		$('#currency_rate_set_action').show();
	});

	$(document.body).on('click', ".currency-rate-item-edit", function(e) {
		var btn = $(this);
		var editBtns = $('.currency-rate-item-edit');
		var setAction = $('#currency_rate_set_action');

		if (editBtns.length) {
			editBtns.each(function () {
				$(this).attr('disabled', true);
			});
		}
		var parent_item = btn.parents('.wooaio-currency-item');
		editedHtml = parent_item.html();
		parent_item.removeClass('summary-view-item').removeClass('edit-view-item').addClass('edit-view-item');
		setAction.hide();
	});

	$(document.body).on('click', ".currency-rate-item-change-cancel", function(e) {
		var btn = $(this);
		var setAction = $('#currency_rate_set_action');

		var parent_item = btn.parents('.wooaio-currency-item');
		parent_item.removeClass('summary-view-item').removeClass('edit-view-item').addClass('summary-view-item');
		parent_item.html(editedHtml);

		var editBtns = $('.currency-rate-item-edit');

		if (editBtns.length) {
			editBtns.each(function () {
				$(this).attr('disabled', false);
			});
		}
		setAction.show();
	});

	$(document.body).on('click', ".currency-rate-item-create, .currency-rate-item-update", function(e) {
		var formData = $("#wooaio-currency-rate-settings").serialize();
		var data = {
			formData: formData,
			action: 'wooaiocurrency_create_currency_rate'
		};

		ajaxRequest(data);
	});

	$(document.body).on('change', ".apply_for_radio", function(e) {
		var radio = $(this);
		var item = radio.parents('.wooaio-currency-item');
		var selected = item.find('.apply_for_radio:checked').val();
		var applyByContainer = item.find('.apply_by_container');
		applyByContainer.removeClass('apply_for_specified_categories').removeClass('apply_for_specified_products');

		if (selected == 'specified_categories') {
			applyByContainer.addClass('apply_for_specified_categories');
		} else if(selected == 'specified_products') {
			applyByContainer.addClass('apply_for_specified_products');
		}
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
