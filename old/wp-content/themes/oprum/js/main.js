/*globals WPURLS */

jQuery(function($) {

	/**
	 * Enable flexslider
	 */
	$('.flexslider').flexslider({
		animation: "slide",
		controlNav: false,
		pauseOnHover: true
    });
	/**
	 * Enable FitVids.js
	 */
	$("#content").fitVids();
	/**
	 * ScrollTop
	 */
	$('a[href=#toplink]').click(function(){
		$('html, body').animate({scrollTop:0}, 200);
		return false;
	});
	
});
