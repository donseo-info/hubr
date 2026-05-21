/**
 * Wpshop Customizer Control Sortable Checkboxes
 *
 * @version     1.0.1
 *
 * Changelog
 *
 * 1.0.1    2019-01-27      Change names
 * 1.0.0    2019-01-21      Init
 */

jQuery(function($) {

    /**
     * Sortable
     */
    $('.wpshop-customize-sortable-checkboxes-list').sortable({
        handle: '.wpshop-customize-sortable-checkboxes-handle',
        axis: 'y',
        update: function (e, ui) {
            $('.wpshop-customize-sortable-checkboxes-item').trigger('change');
        }
    });


    /**
     * Change value
     */
    $('.wpshop-customize-sortable-checkboxes-item').on('change', function () {

        /* Get the value, and convert to string. */
        var this_checkboxes_values = $(this).parents('.wpshop-customize-sortable-checkboxes-list').find('.wpshop-customize-sortable-checkboxes-item').map(function () {
            var active = '0';
            if ($(this).prop("checked")) {
                active = '1';
            }
            if (active ==='1') {
                return this.name;
            }
        }).get().join(',');

        $(this).parents('.wpshop-customize-sortable-checkboxes-list').find('.wpshop-customize-sortable-checkboxes-field').val(this_checkboxes_values).trigger('change');

    });

});