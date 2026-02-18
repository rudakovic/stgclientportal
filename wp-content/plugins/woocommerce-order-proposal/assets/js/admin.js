jQuery( document ).ready( function( $ ) {

	var wc_order_proposal = {
		// init Class
		init: function() {
			$( '#wc_order_proposal_box' ).on( 'click', 'button.button-save-form', this.save_form );
			$( '#select2-results-2' ).on( 'mouseup', this.change_status );
			$( '#order_status' ).on( 'change', this.change_status_new );
			$( '#dropdown_shop_order_language' ).on( 'change', this.change_language );
			$( '#woocommerce-order-items' ).on( 'click', 'button.order_proposal_reduce_stock', this.reduce_stock );
		},

		reduce_stock: function () {

			$( '#woocommerce-order-items' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			var data = {
				action:      'wc_order_proposal_reduce_stock',
				order_id:    woocommerce_admin_meta_boxes.post_id,
				security:    wpo_wcop.reduce_stock_nonce,
			};

			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
				if ( response['result'] ) {
					location.reload();
				} else {
					console.log(response);
				}
			});

			return false;
		},

		change_language: function () {

			var newlang = $( '#dropdown_shop_order_language' ).val();

			if ( !newlang ) {
				return false;
			}

			var data = {
				action:                   'wc_order_proposal_add_language',
				order_id:                 woocommerce_admin_meta_boxes.post_id,
				wc_order_language:        newlang,
				security:                 wpo_wcop.lang_nonce,
			};

			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

			});

			return false;
		},

		// When a user enters a new tracking item
		save_form: function () {

			var data = {
				action:                        'wc_order_proposal_save_form',
				order_id:                      woocommerce_admin_meta_boxes.post_id,
				wc_order_proposal_time:        $( '#wc_order_proposal_time' ).val(),
				wc_order_proposal_start_time:  $( '#wc_order_proposal_start_time' ).val(),
				wc_order_proposal_prepay:      $( '#wc_order_proposal_prepay' ).prop( "checked"),
				security:                      wpo_wcop.save_nonce,
			};

			$(wpo_wcop.save_data).each(function(j, field) {

				data[field] =  $( '#' + field ).prop( "checked");
			});

			//hide the response box
			$('#wc_order_proposal_box .order_proposal_output').hide();

			$('#wc_order_proposal_box').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

				//there was a problem
				if ( response != 1 ) {
					$('#wc_order_proposal_box .order_proposal_output').text("Sorry there was an error");
					$('#wc_order_proposal_box .order_proposal_output').css('color', 'red');
				//response is true
			} else {
				$('#wc_order_proposal_box .order_proposal_output').hide();
				$('#wc_order_proposal_box .order_proposal_output').text("Saved successfully");
				$('#wc_order_proposal_box .order_proposal_output').css('color', 'green');
			}
			$('#wc_order_proposal_box .order_proposal_output').show();
			$('#wc_order_proposal_box').unblock();
		});

			return false;
		},

		change_status: function () {

			var order_proposal_enabled  = $('#wc_order_proposal_box').data( 'enabled' );
			var order_proposal_translate  = $('#wc_order_proposal_box').data( 'proposaltranslate' );
			var status = $( '.select2-hidden-accessible' ).text();

			if (status === order_proposal_translate || order_proposal_enabled === "yes") {
				$('#wc_order_proposal').show();
			} else {
				$('#wc_order_proposal').hide();
			}

		},

		change_status_new: function () {

			var order_proposal_enabled  = $('#wc_order_proposal_box').data( 'enabled' );
			var status = $( '#order_status').val();

			if (status === "wc-order-proposal" || status === "wc-order-proposalreq" || order_proposal_enabled === "yes") {
				$('#wc_order_proposal').show();
			} else {
				$('#wc_order_proposal').hide();
			}
		}
	}

	wc_order_proposal.init();
	wc_order_proposal.change_language();

} );
