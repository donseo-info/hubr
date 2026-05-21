/**
 * Wpshop Customizer Control Page Constructor
 *
 * 1.0.1    2019-05-25      Change prepend on append
 * 1.0.0    2019-04-10      Init
 */

jQuery(function($) {

    /**
     * Sortable
     */
    $('.wpshop-customize-pc-items').sortable({
        handle: '.wpshop-customize-pc-item-header',
        axis: 'y',
        update: function (e, ui) {
            wpshop_customize_pc_update( $(this).find('input:first') );

            //var $field = $container.find('.wpshop-customize-typography-field');
            //$field.val( JSON.stringify(new_val) ).trigger('change');

        }
    });


    function wpshop_customize_pc_update( $el ) {
        var $container = $el.parents('.customize-control-page-constructor');
        var $items = $container.find('.wpshop-customize-pc-items');
        var $field = $container.find('.wpshop-customize-pc-field');
        var sections = [];

        setTimeout(function(){

            $items.find('.wpshop-customize-pc-item').each(function(){
                var $item = $(this);

                var section = {};
                $item.find('input, textarea, select').each(function(){
                    var $el = $(this);
                    var val = $el.val();

                    // if checkbox
                    if ( $el.attr('type') === 'checkbox' ) {
                        val = $el.is(':checked');
                    }
                    section[$el.attr('name')] = val;
                });

                sections.push(section)
            });

            $field.val( JSON.stringify(sections) ).trigger('change');

        }, 50);
    }

    $(document).on('change', '.wpshop-customize-pc-item input, .wpshop-customize-pc-item select, .wpshop-customize-pc-item textarea', function() {
        wpshop_customize_pc_update( $(this) );
    });

    var timer_settings_update;
    $(document).on('keyup', '.wpshop-customize-pc-item input, .wpshop-customize-pc-item textarea', function(){
        var $this = $(this);
        clearTimeout(timer_settings_update);
        timer_settings_update = setTimeout(function(){
            wpshop_customize_pc_update( $this );
        },500);
    });

    // layer name
    $(document).on('keyup', '.wpshop-customize-pc-item input[name=section_header_text]', function(){
        var $this = $(this);
        var $items = $this.parents('.wpshop-customize-pc-item');

        $items.find('.wpshop-customize-pc-item-header em').text( $this.val() );
    });


    /**
     * Presets
     */
    $(document).on('click', '.wpshop-customize-pc-preset', function () {
        var $this = $(this);
        var $presets = $this.parent();

        $presets.find('.wpshop-customize-pc-preset').removeClass('active');
        $this.addClass('active');

        $presets.find('input[name=preset]').val( $this.data('preset-name') ).change();
    });


    /**
     * Open
     */
    $(document).on('click', '.wpshop-customize-pc-item-header', function () {
        var $container = $(this).parent();
        $container.find('.wpshop-customize-pc-item-body').slideToggle();
    });


    /**
     * Add
     */
    $('.js-wpshop-customize-pc-add').on('click', function () {
        var $this = $(this);
        var $container = $this.parents('.customize-control-page-constructor');
        var $items = $container.find('.wpshop-customize-pc-items');
        var type = $this.data('type');

        var copy = $container.find('.wpshop-customize-pc-placeholders .wpshop-customize-pc-item[data-type=' + type + ']').clone();
        $items.append( copy );

        wpshop_customize_pc_update( $items.find('input:first') );
    });


    /**
     * Delete
     */
    $(document).on('click', '.js-wpshop-customize-pc-delete', function(){
        var $this = $(this);
        var $container = $this.parents('.customize-control-page-constructor');
        var $items = $container.find('.wpshop-customize-pc-items');
        $this.parents('.wpshop-customize-pc-item').remove();
        wpshop_customize_pc_update( $items );
    });


    /**
     * Open
     */
    $(document).on('click', '.wpshop-customize-pc-post-card-settings-open', function(){
        var $item = $(this).parents('.wpshop-customize-pc-item');
        $item.find('.wpshop-customize-pc-post-card-settings').slideToggle();
    });

});