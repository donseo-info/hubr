/**
 * Table of Contents
 *
 * @version   1.0.1
 *
 * Changelog
 *
 * 1.0.0    2020-10-18      init
 *
 * 1.0.1    2020-07-14      Add support lazyload images and other dynamic elements.
 *                          When scroll animate update finish coordinates in step.
 *                          Пока скролл летит к элементу — обновляем координаты финальной точки,
 *                          т.к. в процессе полета могут загружаться новые блоки и менять итоговые координаты
 *
 * 1.0.2    2020-09-23      Add import function createCookie
 */

import {createCookie} from './cookies';

export function init_toc($) {
    "use strict";

    $(document).on('click', '.js-table-of-contents-hide', function () {

        var $toc = $(this).parents('.table-of-contents');

        $toc.toggleClass('open');
        if ($toc.hasClass('open')) {
            createCookie('wpshop_toc_hide', "", -1);
            $('.js-table-of-contents-list').slideDown();
        } else {
            createCookie('wpshop_toc_hide', 'hide');
            $('.js-table-of-contents-list').slideUp();
        }

    });

    $(document).on('click', '.table-of-contents a[href*="#"]', function (e) {
        var fixed_offset = 100;
        var $target = $(this).parents(".entry-content").find(this.hash);

        $('html,body').stop().animate({
            scrollTop: $target.offset().top - fixed_offset
        }, {
            duration: 500,
            step: function( now, fx ) {

                var offset = $target.offset().top - fixed_offset;
                if ( fx.end !== offset ) {
                    fx.end = offset;
                }

            },
        });
        e.preventDefault();
    });

}
