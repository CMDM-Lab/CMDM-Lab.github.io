"use strict";

jQuery( document ).ready( function () {

	var gadwp_ui = {
		action : 'gadwp_dismiss_notices',
		gadwp_security_dismiss_notices : gadwp_ui_data.security,
	}

	jQuery( "#gadwp-notice .notice-dismiss" ).click( function () {
		jQuery.post( gadwp_ui_data.ajaxurl, gadwp_ui, function ( response ) {
			return true;
		} );
	} );
	
	if (gadwp_ui_data.ed_bubble != ''){
		jQuery('#toplevel_page_gadash_settings li > a[href*="page=gadash_errors_debugging"]').append('&nbsp;<span class="awaiting-mod count-1"><span class="pending-count" style="padding:0 7px;">'+gadwp_ui_data.ed_bubble+'</span></span>');
	}	
	
} );