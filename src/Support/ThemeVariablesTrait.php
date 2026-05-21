<?php

namespace WPShop\WPCommunity\Support;

use WPShop\WPCommunity\Customizer\Customizer;
use function WPShop\WPCommunity\theme_container;

trait ThemeVariablesTrait {

    /**
     * @var array|null
     */
    protected $_scheme_array;

    /**
     * @param string $variable
     *
     * @return string
     */
    public function get_theme_color( $variable ) {
        if ( null == $this->_scheme_array ) {
            $this->_scheme_array = [];

            $customizer = theme_container()->get( Customizer::class );

            if ( $scheme = $customizer->get_option( 'color_scheme' ) ) {
                $scheme = json_decode( $scheme, true );

                if ( json_last_error() == JSON_ERROR_NONE ) {
                    $this->_scheme_array = $scheme;
                }
            }
        }

        if ( isset( $this->_scheme_array['palettes']['light'][ $variable ] ) ) {
            return $this->_scheme_array['palettes']['light'][ $variable ];
        }

        return '';
    }
}
