/**
 * Identity.js
 *
 * Some custom scripts for this theme.
 */
( function( $ ) {

	/*--------------------------------------------------------------
	Javascript enabled.
	--------------------------------------------------------------*/
	
	// Add js-enabled class to body.
	$( 'body' ).addClass( 'js-enabled' );

	/*--------------------------------------------------------------
	Search toggle button.
	--------------------------------------------------------------*/

	// Toggle search button.
	$( ".search-toggle" ).click(function(){
		$( "body" ).toggleClass( "show-desktop-search" );
	});

	/*--------------------------------------------------------------
	Sidebar height.
	--------------------------------------------------------------*/

	/**
	 * Adds a top margin to the footer if the sidebar widget area is higher
	 * than the rest of the page, to help the footer always visually clear
	 * the sidebar.
	 */
	function identityFitSidebar() {
		var body = $( 'body' ),
			wind = $( window );

		if ( body.is( '.left-sidebar' ) || body.is( '.right-sidebar' ) ) {

			var margin = $( '#secondary .widget-area' ).height() - $( '#content' ).height();

			if ( margin > 0 && wind.innerWidth() > 999 ) {
				$( '#colophon' ).css( 'margin-top', margin + 'px' );
			}
		}
	}

	$(window).load(identityFitSidebar);

	/*--------------------------------------------------------------
	Hidden header heights
	--------------------------------------------------------------*/

	function identityHeightHeader() {
		// Get the menu-container height.
		var menuHeight = $("#mobile-navigation").height();

		// Get the desktop search container height.
		var searchHeight = $("#desktop-search").height();

		// Append style with these heights to the header.
		$( "<style>.js-enabled.toggled .header-hidden { height:" + menuHeight + "px; } .js-enabled.show-desktop-search .header-hidden { height:" + searchHeight + "px; }</style>" ).appendTo( "head" );
	}

	$(document).ready(identityHeightHeader);

	/*--------------------------------------------------------------
	Close hidden header
	--------------------------------------------------------------*/

	// The remove class function.
	function identityCloseHeader() {
		$("body").removeClass("toggled show-desktop-search");
	}

	// Remove class on window resize.
	$(window).on('resize',identityCloseHeader);

	// Remove class with the Escape key.
	$( document ).keyup( function( e ) {
		if ( e.keyCode === 27 && ( $( 'body' ).hasClass( 'toggled' ) || $( 'body' ).hasClass( 'show-desktop-search' ) ) ) {
			identityCloseHeader();
		}
	} );

	/*--------------------------------------------------------------
	Accessibility fixes.
	--------------------------------------------------------------*/

	// Add a focus class to sub menu items with children.
	$( ".menu-item-has-children" ).on( 'focusin focusout', function() {
		$( this ).toggleClass( "focus" );
	});

	// Make focus search-toggle more intuitif.
	$('.search-toggle').click(function(){

		// Only change focus if search-toggle is toggled.
		if ( $( "body" ).hasClass( "show-desktop-search" ) ) {

			// Move focus to search-input.
			$( ".search-toggle" ).on( 'blur', function() {
				$( ".search-input" ).focus();
			});

			// Move focus to search-toggle.
			$( ".search-submit" ).on( 'blur', function() {
				$( ".search-toggle" ).focus();
			});
		}
	});

	// Make focus menu-toggle more intuitif.
	$('.menu-toggle').click(function(){

		// Only change focus if menu-toggle is toggled.
		if ( $( "body" ).hasClass( "toggled" ) ) {

			// Move focus to first menu item.
			$( ".menu-toggle" ).on( 'blur', function() {
				$( '#mobile-navigation' ).find( 'a:eq(0)' ).focus();
			});

			// Move focus to menu-toggle.
			$( ".search-submit" ).on( 'blur', function() {
				$( ".menu-toggle" ).focus();
			});
		}
	});

	/*--------------------------------------------------------------
	Back-To-Top.
	--------------------------------------------------------------*/

	// Check to see if the window is top if not then display back-to-top button.
	$(window).scroll(function(){
		if ($(this).scrollTop() > 500) {
			$(".back-to-top").addClass("show-back-to-top");
		} else {
			$(".back-to-top").removeClass("show-back-to-top");
		}
	});

	//Click event to scroll to top.
	$('.back-to-top').click(function(){
		$('html, body').animate({scrollTop : 0},800);
		return false;
	});
	
})( jQuery );