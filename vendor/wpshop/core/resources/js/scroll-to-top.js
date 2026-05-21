/**
 * Scroll to top
 *
 * @version   1.0
 * @updated   2018-10-18
 */

jQuery(function($) {
    "use strict";

    var width = $(window).width();

    var $scroll_btn = $(".js-scrolltop");

    $scroll_btn.on('click', function () {
        return $("body,html").animate({
            scrollTop: 0
        }, 500);
    });

    $(window).on('scroll', function () {
        if ( $(this).scrollTop() > 100 ) {
            if (width < 991) {
                if ( $scroll_btn.data('mob') === 'on' ) {
                    $scroll_btn.fadeIn();
                }
            } else {
                $scroll_btn.fadeIn();
            }
        } else {
            $scroll_btn.fadeOut();
        }
    });

});