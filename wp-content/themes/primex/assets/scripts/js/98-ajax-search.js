(function ($) {

    var timer;
    var input;

    $(document.body).on('keyup', '.ajax-search-field', function() {
        input = $(this);
        resetTimer();

        timer = setTimeout(ajaxSearch, 1000);
    });

    $(document).ready(function() {
        var searchInput = $('.ajax-search-field');

        if (searchInput.length) {
			searchInput.each(function() {
				var input = $(this);
				var parent = input.parents('form');
				var holder = "<div class='ajax-search-holder'></div>";
				parent.append(holder);
			});
		}

    });

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});

    function ajaxSearch() {
        console.log('Form input');
        var q = input.val();
        var parent = input.parents('form');
        var holder = parent.find('.ajax-search-holder');

        var data = {
            q: q,
			action: 'snth_ajax_search'
		};

		ajaxRequest(data, function(decoded) {
            if (decoded.searchResult) {
                holder.html(decoded.searchResult).show();
            } else {
				holder.html('').show();
			}
        });
    }

	function ajaxRequest(data, cb, cbError) {
		$.ajax({
			type: 'post',
			url: snthAjaxObj.ajaxurl,
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
					if (decoded.consoleLog) {
						console.log(decoded.consoleLog);
                    }

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

    function resetTimer() {
        console.log('Reset timer');
        clearTimeout( timer );
    }
}(jQuery));