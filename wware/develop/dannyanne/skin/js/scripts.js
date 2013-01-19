jQuery.noConflict();
jQuery(document).ready(function($) {
	jQuery("#slideshow").css("overflow", "hidden");
	
	jQuery("ul#slides").cycle({
		fx: 'fade',
		pause: 1,
		prev: '#prev',
		next: '#next'
	});
	
	jQuery("#slideshow").hover(function() {
    	jQuery("ul#slideNavi").fadeIn();
  	},
  		function() {
    	jQuery("ul#slideNavi").fadeOut();
  	});
	
});