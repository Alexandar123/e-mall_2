require([
	'jquery'
], function ($) {

	$(document).ready(function(){
		calAndSetHeightHeaderFooter();
	});

	$(window).on('resize', function(){
		calAndSetHeightHeaderFooter();
	});

	/* Header Sticky Menu */
	if($('.active-sticky').length){
		$(window).on('scroll', function(){
			if($(this).scrollTop() > $('.active-sticky').height()){
				$('.active-sticky').addClass('scrolling');
			}else {
				$('.active-sticky').removeClass('scrolling');
			}
		});
	}
	/* +++++++ */

    /* Calculate height & set it for header & footer */
    function calAndSetHeightHeaderFooter() {
        require([
            'jquery'
        ], function ($) {
            var $heightHeader = $('header.page-header > div').height();
            $('header.page-header').height($heightHeader);
            if($('.footer-parallax').length){
                var $heightFooter = $('footer.page-footer .footer-parallax').height();
                $('footer.page-footer').height($heightFooter);
            }
        });
    }
    /* +++++++ */

});

