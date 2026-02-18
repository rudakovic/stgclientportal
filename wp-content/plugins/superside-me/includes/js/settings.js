/*
 * Copyright (c) 2019-2020 Robin Cornett
 */

;(function ( document, $, undefined ) {
	'use strict';

	var ssmeSettings = {},
	    table        = '#ssme-custom-buttons';

	/**
	 * Watch the table add/remove buttons for clicks.
	 */
	ssmeSettings.init = function () {
		_countUpdateButtons();
		_sortTable();
		$( '#ssme-add-button' ).on( 'click', _addRow );
		$( document.body ).on( 'click', table + ' .ssme-remove-button', _removeRow );
	};

	/**
	 * Make the settings "table" sortable.
	 *
	 * @private
	 */
	function _sortTable() {
		$( table ).sortable( {
			axis: 'y',
			containment: 'parent',
			items: '.button-row',
			tolerance: 'pointer',
			stop: function ( event, ui ) {
				_maybeRename();
			}
		} );
	}

	/**
	 * Add a new row.
	 * @returns {boolean}
	 * @private
	 */
	function _addRow( e ) {
		e.preventDefault();
		var row = $( table + ' .button-row:last-of-type' );
		var count = row.parent().find( '.button-row' ).length-1;
		var clone = row.clone();
		clone.find( 'input' ).not( ':input[type=checkbox]' ).val( '' );
		clone.find( '[type="checkbox"]' ).attr( 'checked', false );
		clone.find( '.icon-preview span' ).remove();
		clone.find( 'input' ).not( '[type="search"]' ).each( function () {
			this.name = this.name.replace( count, count+1 );
			this.id   = this.id.replace( count, count+1 );
		} );
		clone.find( 'label' ).each( function () {
			var name = $( this ).attr( 'for' );
			$( this ).attr( 'for', _addReplace( name, count+1 ) );
		} );
		clone.insertAfter( row );
		_countUpdateButtons();
		return false;
	}

	/**
	 * Replace the value in a string when adding a row.
	 * @param value
	 * @param count
	 * @private
	 */
	function _addReplace( value, count ) {
		return value.replace( /(\d+)/, parseInt( count ) );
	}

	/**
	 * Remove a row.
	 * @returns {boolean}
	 * @private
	 */
	function _removeRow( e ) {
		e.preventDefault();
		if ( ! confirm( ssmeSettings.params.removeMessage ) ) {
			return false;
		}
		var rows  = $( table + ' .button-row:visible' );
		var count = rows.length;

		if ( count === 1 ) {
			$( table + ' input[type="text"]' ).val( '' );
			$( table + ' input[type="number"]' ).val( '' );
			$( table + ' input[type="checkbox"]' ).attr( 'checked', false );
		} else {
			$( this ).closest( '.button-row' ).remove();
		}

		_maybeRename();
		_countUpdateButtons();
	}

	/**
	 * Maybe cycle through and rename inputs.
	 * @private
	 */
	function _maybeRename() {
		$( table + ' .button-row' ).each( function ( rowIndex ) {
			$( this ).children().find( 'input' ).each( function () {
				var name = $( this ).attr( 'name' );
				name = name.replace( /\[(\d+)\]/, '[' + ( rowIndex ) + ']' );
				$( this ).attr( 'name', name ).attr( 'id', name );
			} );
		} );
	}

	/**
	 * Based on how many button rows we have, show/hide the add/remove buttons.
	 * @private
	 */
	function _countUpdateButtons() {
		var rows   = $( table + ' .button-row:visible' ),
		    count  = rows.length,
		    action = 'show';

		if ( count >= ssmeSettings.params.maxButtons ) {
			action = 'hide';
		}

		$( '#ssme-add-button' )[action]();
	}

	ssmeSettings.params = typeof SuperSideMeSettings === 'undefined' ? '' : SuperSideMeSettings;
	if ( typeof ssmeSettings.params !== 'undefined' ) {
		ssmeSettings.init();
	}

})( document, jQuery );
