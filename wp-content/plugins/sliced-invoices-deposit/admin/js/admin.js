(function( $ ) {
	'use strict';
	
	/**
	 * make sure dependencies are already loaded
	 */
	if ( typeof window.sliced_invoices === "undefined" ) {
		return;
	}
	
	if ( typeof window.sliced_invoices.hooks === "undefined" ) {
		return;
	}
	
	if ( typeof window.sliced_invoices.hooks.sliced_invoice_totals === "undefined" ) {
		return;
	}
	
	if ( typeof window.Decimal === "undefined" ) {
		return;
	}
	
	
	/**
	 * Begin
	 */
	sliced_invoices.hooks.sliced_invoice_totals.push( function calculate_deposit_stuff() {
		
		if ( typeof sliced_invoices.totals === "undefined" ) {
			sliced_invoices.totals = {};
		}
		
		if ( typeof sliced_invoices.totals.addons === "undefined" ) {
			sliced_invoices.totals.addons = [];
		}
		
		var this_is = $('#sliced_deposit_this_is').val();
		
		if ( typeof this_is === "undefined" ) {
			return;
		}
		
		var output = {
			'original_totals':          JSON.parse(JSON.stringify(sliced_invoices.totals)),
			'this_is':                  this_is,
			'deposit':                  new Decimal( 0 ),
			'balance':                  new Decimal( 0 ),
			'_adjustments':             [{
				'type':   'subtract',
				'source': ( this_is === 'balance' ? 'deposit' : '' ) + ( this_is === 'deposit' ? 'balance' : '' ),
				'target': 'total'
			}],
			'_name':                   'deposit'
		};
		
		// first apply adjustments from any other addons
		if ( typeof output.original_totals.addons !== "undefined" ) {
			$.each( output.original_totals.addons, function( key, addon ) {
				if ( typeof addon._adjustments !== "undefined" ) {
					$.each( addon._adjustments, function( key, adjustment ) {
						//var adjustment = $(this);
						var type   = typeof adjustment.type !== "undefined" ? adjustment.type : false;
						var source = typeof adjustment.source !== "undefined" ? adjustment.source : false;
						var target = typeof adjustment.target !== "undefined" ? adjustment.target : false;
						if ( ! type || ! source || ! target ) {
							return; // if missing required fields, skip
						}
						if ( typeof addon[ source ] === "undefined" ) {
							return; // if can't map source, skip
						}
						if ( typeof output.original_totals[ target ] === "undefined" ) {
							return; // if can't map target, skip
						}
						// recover from our JSON stringify poor man's clone here:
						if ( parseFloat( addon[ source ] ) > 0 ) {
							addon[ source ] = new Decimal( addon[ source ] );
						}
						if ( parseFloat( output.original_totals[ target ] ) > 0 ) {
							output.original_totals[ target ] = new Decimal( output.original_totals[ target ] );
						}
						switch ( type ) {
							case 'add':
								output.original_totals[ target ] = output.original_totals[ target ].plus( addon[ source ] );
								break;
							case 'subtract':
								output.original_totals[ target ] = output.original_totals[ target ].minus( addon[ source ] );
								break;
						}
					});
				}
			});
		}
		
		// add hook
		sliced_invoices.totals.addons.push( output );
		
		// display it
		jQuery("#_sliced_line_items #sliced_deposit_project_total").html( sliced_invoices.utils.formattedAmount( output.original_totals.total ) );
		
	});

})( jQuery );
