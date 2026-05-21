/**
 * Wpshop Customizer Control Typography
 *
 * 1.0.1    2019-05-22      fix reset styles
 * 1.0.0    2018-10-24      init
 */
jQuery(function($) {

    $('.wpshop-customize-typography-style-btn').on('click', function(){
        var $this = $(this);
        $this.toggleClass('active');
        wpshop_customize_typography_update( $(this) );
    });

    jQuery( '.customize-control-typography input[type=number], .customize-control-typography select' ).on( 'change', function() {
        wpshop_customize_typography_update( $(this) );
    });

    $('.wpshop-customize-reset').on('click', function(){
        var default_value = $(this).data('default');
        console.log(default_value);

        var $container = $(this).parents('.customize-control-typography');

        if (typeof default_value["font-family"] !== "undefined") {
            $container.find('.wpshop-customize-typography-font-family select').val(default_value["font-family"])
        }

        if (typeof default_value["font-size"] !== "undefined") {
            $container.find('.wpshop-customize-typography-font-size input:first').val(default_value["font-size"])
        }

        if (typeof default_value["line-height"] !== "undefined") {
            $container.find('.wpshop-customize-typography-line-height input').val(default_value["line-height"])
        }

        if (typeof default_value["color"] !== "undefined") {
            $container.find('.wpshop-customize-typography-color .wp-color-picker').val(default_value["color"]);
            $container.find('.wpshop-customize-typography-color input').trigger('click');
        }

        // reset font style
        // TODO: set default
        $container.find('.wpshop-customize-typography-style-btn').each(function(){
            $(this).removeClass('active');
        });

        wpshop_customize_typography_update( $(this) );
    });

    /*$('.wpshop-customize-typography-color').on('change', 'input', function () {
     wpshop_customize_typography_update( $(this) );
     });*/

    function wpshop_customize_typography_update( $el ) {
        var $container = $el.parents('.customize-control-typography');
        var $field = $container.find('.wpshop-customize-typography-field');
        var types = [];

        setTimeout(function(){
            $container.find('.wpshop-customize-typography-style-btn').each(function(){
                var type = $(this).data('type');
                if ( $(this).hasClass('active') ) {
                    types.push(type);
                }
            });

            var new_val = {
                'font-family': $container.find('.wpshop-customize-typography-font-family select').val(),
                'font-size': $container.find('.wpshop-customize-typography-font-size input').val(),
                'line-height': $container.find('.wpshop-customize-typography-line-height input').val(),
                'unit': $container.find('.wpshop-customize-typography-unit input').val(),
                'font-style': types.join(','),
                'color': $container.find('.wpshop-customize-typography-color .wp-color-picker').val()
            };
            $field.val( JSON.stringify(new_val) ).trigger('change');
        }, 20);
    }

    jQuery('.wpshop-customize-typography-color input').each(function() {

        var $container = $(this).parents('.customize-control-typography');
        var default_value = $container.find('.wpshop-customize-reset').data('default');
        var default_color;
        if (typeof default_value["color"] !== "undefined") {
            default_color = default_value["color"];
        } else {
            default_color = '#111111';
        }

        jQuery(this).wpColorPicker({
            // you can declare a default color here,
            // or in the data-default-color attribute on the input
            defaultColor: default_color,

            // a callback to fire whenever the color changes to a valid color
            change: function (event, ui) {
                wpshop_customize_typography_update( $(this) );
            },
            // a callback to fire when the input is emptied or an invalid color
            clear: function () {
            },
            // hide the color picker controls on load
            hide: true,
            // set  total width
            width: 200,
            // show a group of common colors beneath the square
            // or, supply an array of colors to customize further
            palettes: ['#444444', '#ff2255', '#559999', '#99CCFF', '#00c1e8', '#F9DE0E', '#111111', '#EEEEDD']
        });

    });

} );