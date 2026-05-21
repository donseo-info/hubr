<?php

namespace WPShop\WPCommunity\Support;

use Wpshop\OmniVideo\Admin\Settings;
use WPShop\WPCommunity\Customizer\CssBuilder;
use function Wpshop\OmniVideo\container;

class OmniVideo {

    use ThemeVariablesTrait;

    /**
     * @return void
     */
    public function init() {
        add_action( 'wpcommunity/layout/css', [ $this, '_add_styles' ] );
    }

    /**
     * @param CssBuilder $css
     *
     * @return void
     */
    public function _add_styles( $css ) {
        if ( ! defined( 'WP_OMNIVIDEO_FILE' ) ) {
            return;
        }

        $omnivideo_style = container()->get( Settings::class )->get_value( 'style' );

        if ( in_array( $omnivideo_style, [ 'standard', 'simple-3' ] ) ) {
            if ( $color = $this->get_theme_color( 'wpsc-text-color' ) ) {
                $color = sanitize_hex_color( $color );
                $css->new_rule( 'body' )
                    ->add_property( '--omnivideo-header-color', $color )
                    ->add_property( '--omnivideo-block-color', $color )
                ;
            }
        }
    }
}
