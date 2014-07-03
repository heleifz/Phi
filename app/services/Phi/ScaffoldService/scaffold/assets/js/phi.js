// Functions

/**
 * Initialization
 */

 function initTransparentNavbar(opacity, element)
 {
 	$(window).on('scroll', function () {
	 	var offset = $(document).scrollTop();
	 	if (offset === 0) {
	 		element.css({'opacity' : 1.0});
	 	} else {
	 		element.css({'opacity' : opacity});
	 	}
 	});
 	element.on('mouseenter', function () {
 		$(this).clearQueue().animate({'opacity' : 1.0}, 'fast');
 	}).on('mouseleave', function () {
 		if ($(document).scrollTop() !== 0)
 		{
	 		$(this).clearQueue().animate({'opacity' : opacity}, 'fast');
 		}
 	});
 }

function init()
{
	hljs.initHighlightingOnLoad();
	initTransparentNavbar(0.5, $('#navigation'));
}


$(function() {
	init();
});