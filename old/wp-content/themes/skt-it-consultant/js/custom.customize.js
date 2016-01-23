jQuery(document).ready(function(e) {
    jQuery('.accordion-section-title').eq(0).after('<a style="position:relative;left:15px;padding:2px 10px;bottom:31px;background:#b40000;color:#ffffff;" href="http://www.sktthemes.net/shop/consultant-wordpress-theme/" target="_blank">Upgrade to PRO Version</a>');
	jQuery('.accordion-section-title').eq(0).css('padding-bottom','35px');
	jQuery('li#accordion-panel-widgets ul li .accordion-section-content').append('<br><strong>More Widgets in <a href="http://www.sktthemes.net/shop/consultant-wordpress-theme/" target="_blank">PRO Version</a></strong>');
});