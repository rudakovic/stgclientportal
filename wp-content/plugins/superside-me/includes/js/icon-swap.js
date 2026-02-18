/*
 * Copyright (c) 2019-2020 Robin Cornett
 */

(function ( document, $, undefined ) {
	'use strict';

	var input = '.ssme-iconpicker',
	    table = '.icons-container';

	$( input ).each( function () {
		_initialize( this );
	} );

	$( '#ssme-add-button' ).on( 'click', function () {
		setTimeout( function () {
			var $row      = $( '.button-row:last-of-type' ),
			    new_input = $row.find( input );
			_initialize( new_input );
		}, 500 );
	} );

	$( table ).on( 'iconpickerSelect', input, function ( e ) {
		if ( ! e.target.attributes.class.value.includes( 'ssme-iconpicker' ) ) {
			return;
		}
		var icon = e.iconpickerItem.attr( 'title' ),
			new_value = icon.replace( '.', '' ),
		    preview   = $( e.target ).next().find( 'span' );
		preview.attr( 'class', 'fa icon fa-' + new_value );
	} );

	/**
	 * Initialize the iconpicker.
	 * @param element
	 * @private
	 */
	function _initialize( element ) {
		if ( $( element ).siblings( '.popover' ).length > 0 ) {
			return;
		}
		var icons = _getIcons();
		if ( $( '#ssme-custom-buttons' ).length > 0 ) {
			icons = _getIcons().concat( _getBrands() );
		}
		$( element ).after( '<div class="icon-preview"><span class="fa icon fa-' + $( element ).val() + '"></span></div>' );
		$( element ).iconpicker( {
			placement: 'bottom',
			hideOnSelect: true,
			icons: icons.sort(),
			animation: false,
			selectedCustomClass: 'selected',
			inputSearch: true,
			fullClassFormatter: function ( a ) {
				return 'fa fa-' + a;
			}
		} );
	}

	/**
	 * Get the list of icons.
	 * @returns array
	 * @private
	 */
	function _getIcons() {
		return SuperSideMeIcons.icons;
	}

	/**
	 * Get the list of brand icons.
	 * Used only on the custom buttons screen.
	 * @returns array
	 * @private
	 */
	function _getBrands() {
		return SuperSideMeIcons.brands;
	}

})( document, jQuery );
