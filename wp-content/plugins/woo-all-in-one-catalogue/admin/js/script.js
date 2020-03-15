(function( $ ) {
	'use strict';
	var actionContainer = $('#wooaio-discount-create-action');
	var formContainer = $('#wooaio-discount-create-container');
	var editedHtml = '';

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

	$(document.body).on('click', ".update-discount-submit", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var form = btn.data('form');
		var setting = btn.data('setting');
		var formData = $("#" + form).serializeArray();

		var data = {
			id: id,
			formData: formData,
			setting: setting,
			action: 'wooaiodiscount_update_product_discount_rule'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', "#add-discount-amount", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var index = $('.wooaio-discount-amount-item').length;

		var data = {
			id: id,
			index: index,
			action: 'wooaiodiscount_add_discount_amount_item'
		};

		ajaxRequest(data, function(decoded) {
			if (decoded.template) {
				var template = $(decoded.template);
				$('#price_product_discount_set_action').hide();

				$('.change-discount-amount').each(function() {
					$(this).attr('disabled', true);
				});
				$('.delete-discount-amount').each(function() {
					$(this).attr('disabled', true);
				});

				$('#price_product_discount_set').append(template);
			}
		});
	});

	$(document.body).on('click', ".create-discount-amount, .update-discount-amount", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var formData = btn.parents('form').serialize();

		var data = {
			id: id,
			formData: formData,
			action: 'wooaiodiscount_create_discount_amount_item'
		};

		ajaxRequest(data);
	});

	/**
	 * Currency rule for discount
	 */
	$(document.body).on('click', ".add-discount-currency-rate", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var currencyCode = btn.data('currency-code');
		var currencyContainer = btn.parents('.wooaio-discount-currency-item');

		var data = {
			id: id,
			currencyCode: currencyCode,
			action: 'wooaiodiscount_add_discount_currency_rate'
		};

		ajaxRequest(data, function(decoded) {
			if (decoded.template) {
				var template = $(decoded.template);

				var actionContainer = btn.parents('.wooaio-discount-currency-item-action');
				actionContainer.hide();

				var editBtns = $('.currency-rate-item-edit');
				var deleteBtns = $('.currency-rate-item-change-delete');

				if (editBtns.length) {
					editBtns.each(function() {
						$(this).attr('disabled', true);
					});
				}

				if (deleteBtns.length) {
					deleteBtns.each(function() {
						$(this).attr('disabled', true);
					});
				}

				// $('#currency_rate_set_action').hide();
				currencyContainer.find('.wooaio-discount-currency-rate-items').append(template);
			}
		});
	});

	$(document.body).on('click', ".currency-rate-item-create, .currency-rate-item-update", function(e) {
		var btn = $(this);
		var currencyContainer = btn.parents('.wooaio-discount-currency-item');
		var id = currencyContainer.data('id');
		var currencyCode = currencyContainer.data('currency-code');
		var formData = btn.parents('.general_product_discount_currency_settings-form').serialize();

		var data = {
			id: id,
			currencyCode: currencyCode,
			formData: formData,
			action: 'wooaiodiscount_create_discount_currency_rate'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', ".currency-rate-item-cancel", function(e) {
		var btn = $(this);
		var currencyContainer = btn.parents('.wooaio-discount-currency-item');
		var currencyRateContainer = btn.parents('.wooaio-currency-rate-item');
		currencyRateContainer.remove();

		var editBtns = $('.currency-rate-item-edit');
		var deleteBtns = $('.currency-rate-item-change-delete');

		if (editBtns.length) {
			editBtns.each(function() {
				$(this).attr('disabled', false);
			});
		}

		if (deleteBtns.length) {
			deleteBtns.each(function() {
				$(this).attr('disabled', false);
			});
		}

		var actionContainer = currencyContainer.find('.wooaio-discount-currency-item-action');
		actionContainer.show();
	});

	$(document.body).on('click', ".currency-rate-item-change-delete", function(e) {
		var btn = $(this);
		var currencyContainer = btn.parents('.wooaio-discount-currency-item');
		var currencyRateContainer = btn.parents('.wooaio-currency-rate-item');
		var id = currencyContainer.data('id');
		var currencyCode = currencyContainer.data('currency-code');

		var sureMessage = btn.data('confirm');
		var sure = confirm(sureMessage);

		if (sure) {
			currencyRateContainer.remove();

			var formData = $('#general_product_discount_currency_settings-form-' + currencyCode).serialize();

			var data = {
				id: id,
				currencyCode: currencyCode,
				formData: formData,
				action: 'wooaiodiscount_create_discount_currency_rate'
			};

			ajaxRequest(data);
		}
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

	$(document.body).on('click', ".copy-discount-currency-rate", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var currencyCode = btn.data('currency-code');

		var data = {
			id: id,
			currencyCode: currencyCode,
			action: 'wooaiodiscount_copy_discount_currency_rate'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', ".delete-discount-currency-rate", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var currencyCode = btn.data('currency-code');

		var data = {
			id: id,
			currencyCode: currencyCode,
			action: 'wooaiodiscount_delete_discount_currency_rate'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', ".wooaio-discount-currency-item .currency-rate-item-edit", function(e) {
		var btn = $(this);
		var itemContainer = btn.parents('.wooaio-currency-rate-item');
		itemContainer.removeClass('summary-view-item').addClass('edit-view-item');

		var currencyContainer = btn.parents('.wooaio-discount-currency-item');
		currencyContainer.find('.wooaio-discount-currency-item-action').hide();
	});

	$(document.body).on('click', ".wooaio-discount-currency-item .currency-rate-item-change-cancel", function(e) {
		var btn = $(this);
		var itemContainer = btn.parents('.wooaio-currency-rate-item');
		itemContainer.removeClass('edit-view-item').addClass('summary-view-item');

		var currencyContainer = btn.parents('.wooaio-discount-currency-item');
		currencyContainer.find('.wooaio-discount-currency-item-action').show();
	});

	$(document.body).on('click', ".delete-discount-amount", function(e) {
		var btn = $(this);
		var item = btn.parents('.wooaio-discount-amount-item');
		item.remove();
		var id = btn.data('id');
		var formData = $('#price_product_discount_settings').serialize();

		var data = {
			id: id,
			formData: formData,
			action: 'wooaiodiscount_delete_discount_amount_item'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', ".change-discount-amount", function(e) {
		var btn = $(this);
		var item = btn.parents('.wooaio-discount-amount-item');
		item.removeClass('wooaio-discount-summary-amount-item').addClass('wooaio-discount-edit-amount-item');
		editedHtml = item.html();
		$('#price_product_discount_set_action').hide();
		$('.change-discount-amount').each(function() {
			$(this).attr('disabled', true);
		});
		$('.delete-discount-amount').each(function() {
			$(this).attr('disabled', true);
		});
	});

	$(document.body).on('click', ".cancel-update-discount-amount", function(e) {
		var btn = $(this);
		var item = btn.parents('.wooaio-discount-amount-item');
		item.removeClass('wooaio-discount-edit-amount-item').addClass('wooaio-discount-summary-amount-item');
		item.html(editedHtml);
		editedHtml = '';
		$('#price_product_discount_set_action').show();
		$('.change-discount-amount').each(function() {
			$(this).attr('disabled', false);
		});
		$('.delete-discount-amount').each(function() {
			$(this).attr('disabled', false);
		});
	});

	$(document.body).on('click', ".cancel-discount-amount", function(e) {
		var btn = $(this);
		var item = btn.parents('.wooaio-discount-amount-item');
		item.remove();
		$('#price_product_discount_set_action').show();

		$('.change-discount-amount').each(function() {
			$(this).attr('disabled', false);
		});
		$('.delete-discount-amount').each(function() {
			$(this).attr('disabled', false);
		});
	});

	$(document.body).on('change', ".apply_for_radio", function(e) {
		var radio = $(this);
        var item = radio.parents('.wooaio-discount-amount-item');
        var selected = item.find('.apply_for_radio:checked').val();
        var applyByContainer = item.find('.apply_by_container');
		applyByContainer.removeClass('apply_by_categories').removeClass('apply_separate_products');

		if (selected == 'by_categories') {
			applyByContainer.addClass('apply_by_categories');
		} else if(selected == 'separate_products') {
			applyByContainer.addClass('apply_separate_products');
		}

        // console.log(selected);
	});


	$(document.body).on('click', "#create-user-submit", function(e) {
		var formData = $("#wooaio-discount-create-form").serializeArray();
		var data = {
			formData: formData,
			action: 'wooaiodiscount_create_user_discount_rule'
		};

		ajaxRequest(data);
	});

	$(document.body).on('click', ".delete-user-rule", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var single = btn.data('single');
		var data = {
			id: id,
			single: single,
			action: 'wooaiodiscount_delete_user_discount_rule'
		};

		var sureMessage = btn.data('confirm');
		var sure = confirm(sureMessage);

		if (sure) {
			ajaxRequest(data);
		}
	});

	$(document.body).on('click', ".update-user-submit", function(e) {
		var btn = $(this);
		var id = btn.data('id');
		var form = btn.data('form');
		var setting = btn.data('setting');
		var formData = $("#" + form).serializeArray();

		var data = {
			id: id,
			formData: formData,
			setting: setting,
			action: 'wooaiodiscount_update_user_discount_rule'
		};

		ajaxRequest(data);
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
