function onResize(){
	$("#logo").width( $(window).width()/2 );
	$("#logo").height( $(window).height()/2 );
	$("#logo").center();
}

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", ($(window).height() - $(this).height()) / 4 + "px");
    this.css("left",($(window).width() - $(this).width()) / 2 + "px");
    return this;
}

$(document).ready(function(){
	$(window).trigger('resize');
});

$(window).resize(function() {
	_.debounce( 250, onResize());
});

