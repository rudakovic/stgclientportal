/**
 * SuperSide Me main js engine
 * @package   SuperSideMe
 * @author    Robin Cornett
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */
; ( function ( document, $, undefined ) {
	'use strict';

	var ssme = {},
		sidrEnabled = false,
		supersidemeDefaults = {
			button: 'slide-menu',
			panel: 'side-menu'
		},
		cssClasses = {
			sidr: 'sidr',
			menuButton: 'slide-nav-link',
			submenuToggle: 'sub-menu-toggle',
			menuOpen: 'menu-open',
			searchButton: 'ssme-search',
			buttonWrap: 'ssme-buttons',
			screenReader: 'screen-reader-text',
			body: 'supersideme',
			inner: 'sidr-inner',
			close: 'menu-close'
		},
		cssSelectors = {
			sidr: '.' + cssClasses.sidr,
			panel: '#' + supersidemeDefaults.panel,
			menuButton: '.' + cssClasses.menuButton,
			submenuToggle: '.' + cssClasses.submenuToggle,
			button: '#' + supersidemeDefaults.button,
			searchButton: '.' + cssClasses.searchButton,
			buttonWrap: '.' + cssClasses.buttonWrap,
			searchInput: '.' + cssClasses.buttonWrap + ' .search-me',
			inner: '.' + cssClasses.inner
		},
		ajax = {
			menus: {},
			search: {},
		};

	ssme.init = function () {
		_supersidemeHandler( supersidemeDefaults.button );
	};

	/********************
	 * Private Functions
	 ********************/

	/**
	 * function to handle everything
	 *
	 */
	function _supersidemeHandler ( button ) {

		_doMenuButton( button );
		_maybeAddButtonWrapper();
		_getRESTdata();

		if ( ssme.params.search.button || _isCustomizer() ) {
			_doSearchButton();
		}

		_doCustomButtons();
		if ( cssSelectors.button.length === 0 ) {
			return;
		}

		$( cssSelectors.button ).on( 'click.supersideme', function () {
			_openSesame();
		} );

		if ( $( ssme.params.second.button !== 'undefined' ) ) {
			$( ssme.params.second.button ).on( 'click.second-ssme', function () {
				_getSecondPanel( ssme.params.second );
			} );
		}

		// Close the menu if the window is resized
		$( window ).on( 'resize.supersideme', _doResize ).triggerHandler( 'resize.supersideme' );
	}

	/**
	 * build the main menu button
	 * @return string slide-nav-link button
	 */
	function _doMenuButton ( button ) {

		var buttonText = ssme.params.navarialabel,
			buttonClass = cssClasses.screenReader,
			location = _getLocation(),
			action = ssme.params.function;
		if ( ssme.params.navigation ) {
			buttonText = ssme.params.navigation;
			buttonClass = 'label';
		}
		if ( $( '.genesis-skip-link' ).length > 0 && 'prepend' === action && !location ) {
			location = '.genesis-skip-link';
			action = 'after';
		}
		if ( !location ) {
			location = 'body';
		}
		if ( _isCustomizer() ) {
			buttonText = parent.wp.customize.instance( 'supersideme[navigation]' ).get();
		}
		var wrapper = $( '<div />', {
			class: 'ssme-wrapper'
		} ).append( _getSVG( 'menu' ) )
			.append( $( '<span />', {
				class: buttonClass,
				text: buttonText
			} ) );
		var mainmenuButton = $( '<button />', {
			'aria-pressed': false,
			'aria-expanded': false,
			'disabled': true,
			id: button,
			class: cssClasses.menuButton + ' ssme-button'
		} ).append( wrapper );
		$( location ).first()[ action ]( mainmenuButton );
		$( 'body' ).addClass( cssClasses.body );
	}

	/**
	 * If it's required, add a wrapping container to the button(s).
	 * @private
	 */
	function _maybeAddButtonWrapper () {
		if ( !ssme.params.search.button && 0 === ssme.params.custom.length ) {
			return;
		}

		if ( $( _getLocation() ).length < 1 ) {
			$( cssSelectors.button ).wrap( '<div />' );
		}
		$( cssSelectors.button ).parent().addClass( cssClasses.buttonWrap );
	}

	/**
	 * Gets the menus via ajax/REST.
	 * @since 2.8.0
	 */
	function _getRESTdata() {
		$.ajax( {
			dataType: 'JSON',
			url: ssme.params.rest,
			success: function( response ) {
				ajax.menus  = response.data.menus;
				ajax.search = response.data.search;
			},
			complete: function () {
				$( cssSelectors.button ).attr( 'disabled', false );
				_doSearchButtonForm();
			}
		} );
	}

	/**
	 * Add the search button with the menu button.
	 * @private
	 */
	function _doSearchButton () {
		if ( !ssme.params.search.button ) {
			return;
		}

		var buttonPosition = _isRightSideMenu() ? 'before' : 'after';

		$( cssSelectors.button )[ buttonPosition ]( _getSearchButton() );

		if ( !ssme.params.search.button ) {
			$( cssSelectors.searchButton ).css( 'display', 'none' );
		}

		$( cssSelectors.searchButton ).on( 'click.search', function () {
			$( cssSelectors.searchInput ).slideToggle( 200 );
			_toggleAria( $( cssSelectors.searchButton ), 'aria-pressed' );
		} );
	}

	/**
	 * Build the search button.
	 * @returns {*|tinymce.html.Node}
	 * @private
	 */
	function _getSearchButton () {
		var buttonText = ssme.params.search.button_aria,
			buttonClass = cssClasses.screenReader;
		if ( ssme.params.search.button_text ) {
			buttonText = ssme.params.search.button_text;
			buttonClass = 'label';
		}
		if ( _isCustomizer() ) {
			buttonText = parent.wp.customize.instance( 'supersideme[search_button_text]' ).get();
		}
		var wrapper = $( '<div />', {
			class: 'ssme-wrapper'
		} ).append( _getSVG( 'search' ) )
			.append( $( '<span />', {
				class: buttonClass,
				text: buttonText
			} ) );
		return $( '<button />', {
			'aria-pressed': false,
			id: cssClasses.searchButton,
			class: cssClasses.searchButton,
			disabled: true,
		} ).append( wrapper );
	}

	/**
	 * Add the search form for the search button.
	 * @private
	 */
	function _doSearchButtonForm () {
		var _container = $( cssSelectors.buttonWrap ),
			_location = 'after',
			_position = 'last-of-type',
			_order = '';
		if ( '0px' === _container.css( 'bottom' ) && $.inArray( _container.css( 'position' ), [ 'absolute', 'fixed' ] ) > -1 ) {
			_location = 'before';
			_position = 'first-of-type';
			_order = -5;
		}
		var search = ajax.search;
		if ( ssme.params.search.input ) {
			search = ssme.params.search.input;
		}
		$( cssSelectors.buttonWrap + ' > button:' + _position )[ _location ]( search );
		$( cssSelectors.searchButton ).attr( 'disabled', false );
		$( cssSelectors.searchInput ).css( 'display', 'none' ).css( 'order', _order );
	}

	/**
	 * If there are custom buttons, add them.
	 *
	 * @private
	 */
	function _doCustomButtons () {
		if ( !ssme.params.custom ) {
			return;
		}
		Object.keys( ssme.params.custom ).forEach( function ( key ) {
			if ( ssme.params.custom.hasOwnProperty( key ) ) {
				var current = ssme.params.custom[ key ],
					cssClass = current.label.replace( /\s+/g, '-' ).toLowerCase(),
					labelClass = !current.show && current.icon ? cssClasses.screenReader : 'label',
					args = {
						href: current.link,
						class: 'button ssme-button ssme-custom ssme-' + cssClass
					};
				if ( current.new ) {
					args.target = '_blank';
					args.rel = 'noopener';
				}
				var wrapper = $( '<div />', {
					class: 'ssme-wrapper'
				} ).append( current.icon )
					.append( $( '<span />', {
					class: labelClass,
					text: current.label
				} ) );
				var custom = $( '<a />', args )
					.append( wrapper );
				if ( current.text ) {
					custom.append( current.text );
				}
				var action = _isRightSideMenu() ? 'before' : 'after';
				$( cssSelectors.button )[ action ]( custom );
			}
		} );
	}

	/**
	 * Open the side panel
	 */
	function _openSesame () {
		_getFirstPanel();
		sidrEnabled = true;
		$.sidr( 'open', supersidemeDefaults.panel );
	}

	/**
	 * Close the side panel
	 */
	function _closeSesame () {
		$.sidr( 'close', supersidemeDefaults.panel );
		$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).removeClass( cssClasses.menuOpen ).attr( 'aria-expanded', false ).next( '.sub-menu' ).slideUp( 'fast' );
		return false;
	}

	/**
	 * Main resizing function.
	 */
	var _doResize = _debounce( function () {
		var _button = supersidemeDefaults.button;
		_addBodyClass( _button );
		if ( ssme.params.swipe ) {
			_enableSwipe( _button, 'body' );
		}

		ssme.skipLinks = typeof supersidemeSkipLinks === 'undefined' ? '' : supersidemeSkipLinks;
		if ( typeof ssme.skipLinks !== 'undefined' ) {
			_changeSkipLinks( _button );
		}
		if ( _isDisplayNone( _button ) ) {
			$( cssSelectors.searchInput ).hide();
			_closeSesame();
		}
	}, 250 );

	/**
	 * change body class based on main menu button visibility
	 * @param {id} button main menu button
	 */
	function _addBodyClass ( button ) {
		var _body = $( 'body' );
		if ( _isDisplayNone( button ) ) {
			_body.removeClass( cssClasses.body );
			return;
		}
		_body.addClass( cssClasses.body );
	}

	/**
	 * To cover users without updated CSS--check if sidr is set to display:none and fix.
	 * @private
	 */
	function _checkSidrClass () {
		if ( supersidemeDefaults.panel.length === 0 ) {
			return;
		}
		if ( _isDisplayNone( supersidemeDefaults.panel ) ) {
			var element = document.getElementById( supersidemeDefaults.panel );
			element.style.setProperty( 'display', 'block' );
		}
	}

	/**
	 * enable swiping functionality
	 * @param  {id} button main menu button
	 * @param  {element} body   body element
	 */
	function _enableSwipe ( button, body ) {
		if ( _isDisplayNone( button ) ) {
			$( body ).swipe( 'disable' );
			return;
		}
		$( body ).swipe( 'enable' );
		_touchSwipe( $( body ) );
	}

	/**
	 * Changes skip links, if they exist
	 * @param  {string} button main menu button
	 */
	function _changeSkipLinks ( button ) {
		var _startLink = ssme.skipLinks.startLink,
			_endLink = button,
			_hideLinks = $( ssme.skipLinks.ulClass + ' a[href*="#' + ssme.skipLinks.contains + '"]' ).not( 'a[href*="' + ssme.skipLinks.unique + '"]' );
		if ( _isDisplayNone( button ) ) {
			_startLink = button;
			_endLink = ssme.skipLinks.startLink;
			$( _hideLinks ).removeAttr( 'style' );
		} else {
			$( _hideLinks ).hide();
		}
		var _link = $( ssme.skipLinks.ulClass + ' a[href*="#' + _startLink + '"]' ),
			_target = $( _link ).attr( 'href' );
		if ( !_target ) {
			return;
		}
		_target = _target.replace( _startLink, _endLink );
		$( _link ).attr( 'href', _target );
	}

	/**
	 * define touchSwipe defaults and execute
	 * @param  {element} body what to swipe on
	 */
	function _touchSwipe ( body ) {
		var left = _openSesame,
			right = _closeSesame;
		if ( 'left' === ssme.params.side ) {
			left = _closeSesame;
			right = _openSesame;
		}

		body.swipe( {
			allowPageScroll: 'vertical',
			threshold: 120,
			swipeLeft: left,
			swipeRight: right,
			preventDefaultEvents: false
		} );
	}

	/**
	 * Set up the main menu panel.
	 * @return full sidr panel
	 */
	function _getFirstPanel () {
		var panelSource = null !== ssme.params.source && null !== document.getElementById( ssme.params.source ) ? '#' + ssme.params.source : function () {
			_fillPanel();
		},
			args = {
				button: cssSelectors.button,
				source: panelSource,
				panel: supersidemeDefaults.panel,
				side: ssme.params.side
			},
			submenuButton = '#' + supersidemeDefaults.panel + ' ' + cssSelectors.submenuToggle;

		_buildPanels( args, submenuButton );
	}

	/**
	 * Set up second menu panel.
	 * @param args
	 * @returns {boolean}
	 * @private
	 */
	function _getSecondPanel ( args ) {
		_buildPanels( args, '#' + args.panel + ' ' + cssSelectors.submenuToggle );
		sidrEnabled = true;
		$.sidr( 'open', args.panel );
	}

	/**
	 * Build the menu panel(s).
	 * @param args
	 * @param submenuButton
	 * @returns {boolean}
	 * @private
	 */
	function _buildPanels ( args, submenuButton ) {
		_goSidr( $( args.button ), args );

		if ( $( submenuButton ).length === 0 ) {
			_dosubmenuButtons( args.panel );
			_orphanedParents();
		}
		$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).on( 'click.supersideme-submenu', _submenuToggle );
		_addCloseButton( args.panel, args );

		return false;
	}

	/**
	 * Instantiate the sidr function itself.
	 * @param button
	 * @param args
	 * @private
	 */
	function _goSidr ( button, args ) {
		var status = $.sidr( 'status' );
		if ( status.opened !== args.panel ) {
			$.sidr( 'close', status.opened );
		}
		button.sidr( {
			name: args.panel,
			side: args.side,
			source: args.source,
			renaming: false,
			displace: ssme.params.displace,
			speed: parseInt( ssme.params.speed, 10 ),
			method: 'toggle',
			onOpen: function () {
				button.toggleClass( cssClasses.menuOpen );
				_toggleAria( button, 'aria-pressed' );
				_toggleAria( button, 'aria-expanded' );
				_a11y( button );
			},
			onClose: function () {
				button.removeClass( cssClasses.menuOpen );
				_toggleAria( button, 'aria-pressed' );
				_toggleAria( button, 'aria-expanded' );
				button.focus();
				sidrEnabled = false;
			}
		} );
	}

	/**
	 * add keyboard navigation to the panel
	 * @param  button main menu button
	 *
	 */
	function _a11y ( button ) {
		var navEl = $( cssSelectors.sidr + ' ' + cssSelectors.inner ),
			items = navEl.children(),
			firstItem = items.first(),
			lastItem = items.last();

		/* Thanks to Rob Neu for the following code,
		 all pulled from the Compass theme. */
		// Set the tabindex for the menu container.
		navEl.attr( { tabindex: '0' } );

		// Set focus on the first child item.
		firstItem.focus();

		// When focus is on the menu container.
		navEl.on( 'keydown.sidrNav', function ( e ) {
			// If it's not the tab key then return.
			if ( 9 !== e.keyCode ) {
				return;
			}
			// When tabbing forwards and tabbing out of the last link.
			if ( lastItem[ 0 ] === e.target && !e.shiftKey ) {
				button.focus();
				return false;
			}
			// When tabbing backwards and tabbing out of the first link OR the menu container.
			if ( ( firstItem[ 0 ] === e.target || navEl[ 0 ] === e.target ) && e.shiftKey ) {
				button.focus();
				return false;
			}
		} );
		// When focus is on the toggle button.
		button.on( 'keydown.sidrNav', function ( e ) {
			var status = _checkStatus();
			if ( !status.opened ) {
				return;
			}
			// If it's not the tab key then return.
			if ( 9 !== e.keyCode ) {
				return;
			}
			// when tabbing forwards
			if ( button[ 0 ] === e.target && !e.shiftKey ) {
				firstItem.focus();
				return false;
			}
		} );
	}

	/**
	 * add all the things to the menu panel: navigation, widgets, submenu buttons
	 *
	 */
	function _fillPanel () {
		var container = ssme.params.html5 ? 'nav' : 'div',
			args = ssme.params.html5 ? {
				'class': 'side-navigation',
				'role': 'navigation',
				'itemscope': 'itemscope',
				'itemtype': 'http://schema.org/SiteNavigationElement'
			} : {
					'class': 'side-navigation'
				},
			navigationMenu = $( '<' + container + ' />', args ).append( $( '<ul />', {
				'class': 'side-nav'
			} ) ),
			sidrInner = $( '<div />', {
				'class': cssClasses.inner
			} ),
			_selectors = {
				widget: '.supersideme.widget-area',
				inner: cssSelectors.panel + ' ' + cssSelectors.inner
			};

		// Add navigation menus
		if ( $( _selectors.inner ).length !== 0 ) {
			return;
		}
		$( cssSelectors.panel + cssSelectors.sidr ).prepend( sidrInner );
		var menus = ajax.menus;
		if ( ssme.params.menus ) {
			menus = ssme.params.menus;
		}
		if ( menus ) {
			$( _selectors.inner ).append( navigationMenu );
			$( '.side-nav' ).prepend( menus );
		}
		_checkSidrClass();
		if ( $( _selectors.widget ).length !== 0 ) {
			var _location = ssme.params.widget_end ? 'appendTo' : 'prependTo';
			$( _selectors.widget )[ _location ]( _selectors.inner ).attr( 'style', 'display:block;' );
		}
		if ( ssme.params.search.panel || _isCustomizer() ) {
			var search = ajax.search;
			if ( ssme.params.search.input ) {
				search = ssme.params.search.input;
			}
			$( _selectors.inner ).prepend( search );
			if ( _isCustomizer() && !Boolean( ssme.params.panel ) ) {
				$( '.search-me' ).css( 'display', 'none' );
			}
		}
	}

	/**
	 * add submenu buttons
	 * @param panel
	 * @return submenu toggle buttons adds submenu toggles to menu items with children
	 * @since 1.7.0
	 */
	function _dosubmenuButtons ( panel ) {
		var submenuButton = $( '<button />', {
			'aria-pressed': false,
			'aria-label': ssme.params.subarialabel,
			'class': cssClasses.submenuToggle
		} ).append( _getSVG( 'submenu' ) )
			.append( $( '<span />', {
				'class': cssClasses.screenReader,
				text: ssme.params.submenu
			} ) );

		if ( $( panel + ' ' + cssSelectors.submenuToggle ).length !== 0 ) {
			return;
		}
		$( cssSelectors.sidr + ' .sub-menu' ).before( submenuButton );
		_setElementHeight( cssSelectors.sidr + ' .menu-item-has-children > a', cssSelectors.sidr + ' ' + cssSelectors.submenuToggle );
	}

	/**
	 * for parent menu items which don't actually link to anything of their own, make them into virtual buttons
	 * @since 1.6.0
	 */
	function _orphanedParents () {
		var menuItem = cssSelectors.sidr + ' .menu-item-has-children ';

		$( menuItem + '> a[href="#"], ' + menuItem + '> a:not([href])' )
			.append( _getSVG( 'submenu' ) )
			.addClass( cssClasses.submenuToggle )
			.attr( 'role', 'button' )
			.attr( 'aria-pressed', false )
			.next( cssSelectors.submenuToggle ).remove();
	}

	/**
	 * submenu toggle button behavior. when one opens, others close
	 *
	 */
	function _submenuToggle () {
		var _this = $( this ),
			_others = _this.closest( '.menu-item' ).siblings();
		_toggleAria( _this, 'aria-pressed' );
		_this.toggleClass( cssClasses.menuOpen );
		_this.next( '.sub-menu' ).slideToggle( 'fast' );

		_others.find( cssSelectors.submenuToggle ).removeClass( cssClasses.menuOpen ).attr( 'aria-pressed', false );
		_others.find( '.sub-menu' ).slideUp( 'fast' );
	}

	/**
	 * Add a close button to the Sidr panel.
	 * @param panel
	 * @param args
	 * @private
	 */
	function _addCloseButton ( panel, args ) {
		var id = panel.indexOf( '#' ) <= 0 ? '#' + panel : panel,
			closeButton = _getCloseButton( args );
		if ( $( id + ' .' + cssClasses.close ).length === 0 ) {
			$( id + ' ' + cssSelectors.sidr + '-inner' ).prepend( closeButton );
		}
		$( id + ' ' + ssme.params.closeevent ).on( 'click', function () {
			$( this ).attr( 'aria-pressed', false );
			$.sidr( 'close', panel );
			$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).removeClass( cssClasses.menuOpen ).attr( 'aria-expanded', false ).next( '.sub-menu' ).slideUp( 'fast' );
		} );
	}

	/**
	 * Get the close button, either defined by a filter or built here.
	 * @param panel
	 * @return {jQuery|*}
	 * @private
	 */
	function _getCloseButton ( panel ) {
		if ( !$.isPlainObject( ssme.params.close ) ) {
			return ssme.params.close;
		}
		var text = ssme.params.close.closeAria,
			buttonClass = cssClasses.screenReader;
		if ( ssme.params.close.closeText ) {
			text = ssme.params.close.closeText;
			buttonClass = 'label';
		}
		if ( typeof panel.close !== 'undefined' ) {
			text = panel.close;
			buttonClass = 'label';
		}
		return $( '<button />', {
			'aria-pressed': false,
			'aria-label': ssme.params.close.closeAria,
			'class': cssClasses.close
		} ).append( _getSVG( 'close' ) )
			.append( $( '<span />', {
				'class': buttonClass,
				text: text
			} ) );
	}

	/**
	 * Get the menu button/container location, if it's been set.
	 * @return {boolean}
	 * @private
	 */
	function _getLocation () {
		return $( ssme.params.location ).length > 0 ? ssme.params.location : false;
	}

	/**
	 * If SVG icons are set, get it.
	 * @param icon
	 * @returns {string}
	 * @private
	 */
	function _getSVG ( icon ) {
		return ssme.params.svg ? ssme.params.svg[ icon ] : '';
	}

	/**
	 * generic function to get the display value of an ID element
	 * @param  {id} $id id of an element
	 * @return {boolean}     CSS property value of the element
	 */
	function _isDisplayNone ( $id ) {
		var element = document.getElementById( $id ),
			style = window.getComputedStyle( element );
		return 'none' === style.getPropertyValue( 'display' );
	}

	/**
	 * Toggle aria attributes
	 * @param $this
	 * @param attribute
	 * @private
	 */
	function _toggleAria ( $this, attribute ) {
		$this.attr( attribute, function ( index, value ) {
			return 'false' === value;
		} );
	}

	/**
	 * check whether we are in the customizer window or not
	 * @returns {boolean}
	 * @private
	 */
	function _isCustomizer () {
		return Boolean( ssme.params.customizer );
	}

	/**
	 * Check whether the menu is set to be on the right side.
	 *
	 * @return {boolean}
	 * @private
	 */
	function _isRightSideMenu () {
		return Boolean( 'right' === ssme.params.side );
	}

	/**
	 * Delay action after resize
	 * @param func
	 * @param wait
	 * @param immediate
	 * @returns {Function}
	 * @private
	 */
	function _debounce ( func, wait, immediate ) {
		var timeout;
		return function () {
			var context = this, args = arguments;
			var later = function () {
				timeout = null;
				if ( !immediate ) {
					func.apply( context, args );
				}
			};
			var callNow = immediate && !timeout;
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	/**
	 * Set the height of one element based on the height of another.
	 * @param $selector
	 * @param $before
	 * @private
	 */
	function _setElementHeight ( $selector, $before ) {
		$( $before ).css( 'height', $( $selector ).outerHeight() );
	}

	/**
	 * Check the status of the sidr process.
	 * @private
	 */
	function _checkStatus () {
		return jQuery.sidr( 'status' );
	}

	ssme.params = typeof SuperSideMeVar === 'undefined' ? '' : SuperSideMeVar;

	if ( typeof ssme.params !== 'undefined' ) {
		ssme.init();
	}

} )( document, jQuery );
