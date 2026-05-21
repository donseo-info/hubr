/**
 * Views Counter
 *
 * @version   1.1
 * @updated   1.0 2019-12-02
 * @updated   1.1 2020-04-09 add update_count() on success response
 */

jQuery(function ($) {
    'use strict';

    if (typeof wpshop_views_counter_params !== 'undefined') {
        $.ajax({
            type: "GET",
            url: wpshop_views_counter_params.url,
            data: "id=" + wpshop_views_counter_params.post_id + "&action=wpshop_views_counter",
        }).done(function (response) {
            if (response.success) {
                response.data.forEach(function (item) {
                    update_count(item.id, item.count);
                });
            }
        });
    }

    function update_count(id, text) {
        var $el = $('.js-views-count[data-post_id="' + id + '"]');
        if ($el.length) {
            $el.text(text);
        }
    }
});
