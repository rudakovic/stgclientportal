/**
 * Contains handlers to make Customizer preview reload changes asynchronously.
 */
; ( function ( $ ) {
	'use strict';

	if ( 'undefined' === typeof wp ) {
		return;
	}

	var customize = wp.customize,
		setting = 'supersideme',
		menuButton = '.slide-nav-link',
		searchButton = '.supersideme .ssme-search',
		container = '.ssme-buttons',
		buttons = 1;
	// menu buttons
	customize( setting + '[navigation]', function ( value ) {
		value.bind( function ( to ) {
			var spanClass = to.length ? 'label' : 'screen-reader-text';
			$( menuButton ).children( 'span' ).text( to ).removeClass().addClass( spanClass );
			_changeFlex();
		} );
	} );
	customize( setting + '[close]', function ( value ) {
		value.bind( function ( to ) {
			$( '.menu-close' ).text( to );
		} );
	} );
	// shrink
	customize( setting + '[shrink]', function ( value ) {
		value.bind( function ( to ) {
			var width = '100%',
				element = menuButton;

			if ( _countButtons() > 1 ) {
				element = container;
			}
			if ( 0 < to ) {
				width = 'auto';
			}
			$( element ).css( 'width', width );
			_changeFlex();
		} );
	} );
	// menu buttons
	customize( setting + '[position]', function ( value ) {
		value.bind( function ( to ) {
			var element = _countButtons() > 1 ? container : menuButton,
				position = to,
				bottom = 'auto',
				top = 0;
			if ( 'bottom' === to ) {
				position = 'fixed';
				bottom = 0;
				top = 'auto';
			}
			$( element ).css( 'position', position );
			$( element ).css( 'bottom', bottom );
			$( element ).css( 'top', top );
		} );
	} );
	// background
	customize( setting + '[background]', function ( value ) {
		value.bind( function ( to ) {
			$( '<style type="text/css">.sidr, .slide-nav-link{background-color: ' + to + ';}</style>' ).appendTo( 'head' );
		} );
	} );
	// links
	customize( setting + '[link_color]', function ( value ) {
		value.bind( function ( to ) {
			$( '<style type="text/css">.sidr, .slide-nav-link, .sidr h3, .sidr h4, .sidr .widget, .sidr p, .sidr a, .sidr button {color: ' + to + ';}</style>' ).appendTo( 'head' );
		} );
	} );
	// search
	customize( setting + '[search]', function ( value ) {
		value.bind( function ( to ) {
			var style = false === to ? 'none' : 'block';
			$( '.sidr .search-me' ).css( 'display', style );
		} );
	} );
	customize( setting + '[search_button]', function ( value ) {
		buttons = _countButtons();
		var element = 1 < buttons ? container : menuButton,
			style = $( element ).css( [ 'position', 'bottom' ] );
		value.bind( function ( to ) {
			var display = false === to ? 'none' : 'block';
			$( searchButton ).css( 'display', display );
			var newButtons = _countButtons();
			if ( $( menuButton ).parent().is( 'body' ) ) {
				if ( 0 === buttons && 1 < newButtons ) {
					$( '.slide-nav-link, .ssme-search' ).wrapAll( '<div />' );
				} else if ( 0 < buttons && 0 === newButtons ) {
					$( menuButton ).unwrap( 'ssme-buttons' );
				}
			}
			if ( 1 < newButtons ) {
				$( menuButton ).parent().addClass( 'ssme-buttons' );
			}
			$( element ).css( {
				position: 'relative',
				width: 'auto',
				bottom: ''
			} );
			element = 1 < newButtons ? container : menuButton;
			style[ 'width' ] = _getSetting( 'shrink' ) ? 'auto' : '100%';
			$( element ).css( style );
			_changeFlex();
		} );
	} );
	customize( setting + '[search_button_text]', function ( value ) {
		value.bind( function ( to ) {
			var spanClass = to.length ? 'label' : 'screen-reader-text';
			$( searchButton ).children( 'span' ).text( to ).removeClass().addClass( spanClass );
			_changeFlex();
		} );
	} );

	/**
	 * Get the value of whatever setting.
	 * @param currentSetting
	 * @returns {*}
	 * @private
	 */
	function _getSetting ( currentSetting ) {
		return customize.instance( setting + '[' + currentSetting + ']' ).get();
	}

	/**
	 * Check and update the flex-grow values for search/menu buttons.
	 * @uses _getSetting
	 * @private
	 */
	function _checkButtonsFlex () {
		var autoWidth = Boolean( parseInt( _getSetting( 'shrink' ), 10 ) ),
			menuText = _getSetting( 'navigation' ),
			searchText = _getSetting( 'search_button_text' ),
			flexGrow = {
				menu: 0,
				search: 0
			};
		if ( autoWidth ) {
			return flexGrow;
		}
		if ( !menuText && !searchText ) {
			flexGrow.menu = 1;
			flexGrow.search = 1;
		} else {
			if ( menuText ) {
				flexGrow.menu = 1;
			}
			if ( searchText ) {
				flexGrow.search = 1;
			}
		}
		return flexGrow;
	}

	function _changeFlex () {
		var flexGrow = _checkButtonsFlex();
		$( menuButton ).css( 'flex-grow', flexGrow.menu );
		$( searchButton ).css( 'flex-grow', flexGrow.search );
	}

	function _countButtons () {
		var search = $( '.ssme-search' ).is( ':visible' ) ? 1 : 0;
		return $( '.ssme-button' ).length + search;
	}

} )( jQuery );
