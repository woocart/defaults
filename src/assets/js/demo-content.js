(function($) {

	'use strict';

	// Fire confirm on the onclick event of the button.
	$( '.woocart-remove-products' ).on(
		'click',
		function(e) {
			e.preventDefault();

			var choice       = confirm( woocart_defaults.confirm_text );
			var redirect_url = $( this ).data( 'url' );

			if (choice == true) {
				window.location.replace( redirect_url );
			}
		}
	);

})( jQuery );
