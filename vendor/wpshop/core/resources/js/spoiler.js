/**
 * Spoiler
 *
 * @version   1.0
 * @updated   2018-10-18
 */

jQuery(function($) {
    "use strict";

    $(document).on('click', '.js-spoiler-box-title', function () {
        var $this = $(this);
        $this.toggleClass('active').next().slideToggle();
    });

});