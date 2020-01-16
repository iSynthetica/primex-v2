(function( $ ) {
	'use strict';

	$(document.body).on('click', ".change-currency", function(e) {
		e.preventDefault();
		var control = $(this);
		var currency_code = control.data('currency');
		$.cookie("wooaiocurrency", currency_code, { expires: 7, path: '/' });

		window.location.reload();
	});

})( jQuery );
