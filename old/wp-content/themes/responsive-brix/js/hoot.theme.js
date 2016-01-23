jQuery(document).ready(function($) {
	"use strict";

	/*** Init lightslider ***/

	if (typeof $.fn.lightSlider != 'undefined') {
		$(".lightSlider").each(function(i){
			var self = $(this),
				settings = {
					item: 1,
					slideMove: 1, // https://github.com/sachinchoolur/lightslider/issues/118
					slideMargin: 0,
					mode: "slide",
					auto: true,
					loop: true,
					slideEndAnimatoin: false,
					slideEndAnimation: false,
					pause: 5000,
					adaptiveHeight: true,
					},
				selfData = self.data(),
				responsiveitem = (parseInt(selfData.responsiveitem)) ? parseInt(selfData.responsiveitem) : 2,
				breakpoint = (parseInt(selfData.breakpoint)) ? parseInt(selfData.breakpoint) : 960,
				customs = {
					item: selfData.item,
					slideMove: selfData.slidemove,
					slideMargin: selfData.slidemargin,
					mode: selfData.mode,
					auto: selfData.auto,
					loop: selfData.loop,
					slideEndAnimatoin: selfData.slideendanimation,
					slideEndAnimation: selfData.slideendanimation,
					pause: selfData.pause,
					adaptiveHeight: selfData.adaptiveheight,
					};
			$.extend(settings,customs);
			if( settings.item >= 2 ) { /* Its a carousal */
				settings.responsive =  [ {	breakpoint: breakpoint,
											settings: {
												item: responsiveitem,
												}
											}, ];
			}
			self.lightSlider(settings);
		});
	}

	/*** Superfish Navigation ***/

	if (typeof $.fn.superfish != 'undefined') {
		$('.sf-menu').superfish({
			delay: 500,						// delay on mouseout 
			animation: {height: 'show'},	// animation for submenu open. Do not use 'toggle' #bug
			animationOut: {opacity:'hide'},	// animation for submenu hide
			speed: 200,						// faster animation speed 
			speedOut: 'fast',				// faster animation speed
			disableHI: false,				// set to true to disable hoverIntent detection // default = false
		});
	}

	/*** Responsive Navigation ***/

	$( '.menu-toggle' ).click( function() {
		$( this ).parent().children( '.wrap, .menu-items' ).slideToggle();
		$( this ).toggleClass( 'active' );
	});

	/*** Responsive Videos : Target your .container, .wrapper, .post, etc. ***/

	if (jQuery.fn.fitVids)
		$("#content").fitVids();

});