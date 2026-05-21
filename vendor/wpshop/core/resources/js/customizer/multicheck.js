/**
 * Wpshop Customizer Control Multicheck
 *
 * @version     1.0.0
 *
 * Changelog
 *
 * 1.0.0    2018-10-24      init
 */
jQuery( document ).ready( function() {

    jQuery( '.customize-control-multicheck input[type="checkbox"]' ).on( 'change', function() {

            checkbox_values = jQuery( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map(
                function() {
                    return this.value;
                }
            ).get().join( ',' );

            jQuery( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( checkbox_values ).trigger( 'change' );
        }
    );

} );