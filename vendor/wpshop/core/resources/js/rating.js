/**
 * Rating
 *
 * @version   1.1
 * @updated   2022-05-05 fix use global variable wps_ajax
 * @updated   2020-04-03
 */

jQuery(function($) {
    "use strict";

    var ajax = window.wpshop_ajax || window.wps_ajax;
    if (typeof ajax === 'undefined') {
        console.error('Unable to find ajax parameters for rating scrips');
        return;
    }

    $(document).on('click', '.js-star-rating-item', function(){
        var $this = $(this);
        var $parent = $this.parent();
        var score = $this.data('score');
        var post_id = $parent.data('post-id');
        var rating_count = $parent.data('rating-count');
        var rating_sum = $parent.data('rating-sum');
        var rating_value = $parent.data('rating-value');

        if ( $parent.hasClass('disabled') ) return;

        $parent.addClass('disabled process');

        var ajaxdata = {
            action : 'wpshop_star_rating_submit',
            nonce : ajax.nonce,
            post_id : post_id,
            score : score,
            rating_count: rating_count,
            rating_sum: rating_sum,
            rating_value: rating_value
        };
        jQuery.post( ajax.url, ajaxdata, function( response ) {
            if ( response.success ) {

                rating_sum = rating_sum + score;
                rating_count++;

                rating_value = (rating_sum / rating_count).toFixed(2);

                var rating_count_text = 'assessment';

                var lang = $('html').attr('lang');

                if ( typeof rating_count_text_filter  !== 'undefined' ) {
                    rating_count_text = rating_count_text_filter;
                } else {
                    if ( lang === 'ru-RU' ) {
                        rating_count_text = decOfNum(rating_count, ['оценка', 'оценки', 'оценок']);
                    }

                    if ( lang === 'uk' ) {
                        rating_count_text = decOfNum(rating_count, ['оцінка', 'оцінки', 'оцінок']);
                    }
                }

                $this.parent().parent().find('.star-rating-text').html('<em>( <strong>' + rating_count + '</strong> ' + rating_count_text + ', ' + settings_array.rating_text_average + ' <strong>' + rating_value + '</strong> ' + settings_array.rating_text_from + ' <strong>5</strong> )</em></div>');

            } else {
                if ( response.data === 'already' ) {
                    //alert('already');
                }
                console.log(response);
            }
            $parent.removeClass('process');
        });

        function decOfNum(number, titles) {
            var cases = [2, 0, 1, 1, 1, 2];
            return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
        }

    });

    $('.js-star-rating-item').on({
        mouseenter: function () {
            if ( $(this).parent().hasClass( 'disabled' ) ) return;
            $(this).parent().addClass('hover');
            $(this).addClass('hover').prevAll().addClass('hover');
        },
        mouseleave: function () {
            if ( $(this).parent().hasClass( 'disabled' ) ) return;
            $(this).parent().removeClass('hover');
            $('.js-star-rating-item').removeClass('hover');
        }
    });

});
