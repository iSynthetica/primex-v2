(function( $ ) {
	'use strict';

	$(document.body).on('click touchend', ".change-currency", function(e) {
		e.preventDefault();
		console.log('Clicked');
		var control = $(this);
		var currency_code = control.data('currency');
		var old_currency = $.cookie('wooaiocurrency');
		$.cookie("wooaiocurrency", currency_code, { expires: 7, path: '/' });

		if (typeof wc_cart_fragments_params === 'undefined' || wc_cart_fragments_params === null) {
		} else {
			sessionStorage.removeItem(wc_cart_fragments_params.fragment_name);
		}

		window.location.reload();
	});

	$(document).ready(function () {
		var updateMiniCart = $.cookie('wooaiocurrency_update_minicart');

		if (true) {

			if (typeof wc_cart_fragments_params === 'undefined' || wc_cart_fragments_params === null) {
			} else {
				sessionStorage.removeItem(wc_cart_fragments_params.fragment_name);
			}
			$.removeCookie('wooaiocurrency_update_minicart');
			$.removeCookie('wooaiocurrency');
			// console.log(updateMiniCart);
		}
	});

})( jQuery );
