(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$(function() {
		$(document).on("click", ".imile_show_invoice", function(e){
			e.preventDefault();
			var data = {
				action: 'imile_print_invoice',
				nonce_data : $(this).data( 'nonce' ),
				order_id: $(this).data("order_id")
			};
	
			// Perform the Ajax request
			$.post(myAjax.ajaxurl, data, function(response) {
				// Handle the response from the server
				if(response.imiledata){
					let imiledata = response.imiledata;
					if(imiledata.imileAwb){
						var a = document.createElement("a"); //Create <a>
						a.href = "data:application/pdf;base64," + imiledata.imileAwb; //Image Base64 Goes here
						a.download = "invoice.pdf"; //File name Here
						a.click();
						//window.open(image, "_blank");
					}
				}
				console.log(response);
				// You can update the DOM or display a message based on the response
			});
		});
	});
})( jQuery );
