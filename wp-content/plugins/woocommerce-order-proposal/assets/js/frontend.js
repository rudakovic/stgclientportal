jQuery( document ).ready( function( $ ) {

	$( '.my_account_orders .decline-proposal' ).on( 'click', function() {
		return confirm( wpo_wcop.decline_proposal );
	} );

} );