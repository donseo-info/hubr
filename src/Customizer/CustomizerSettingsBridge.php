<?php

namespace WPShop\WPCommunity\Customizer;

class CustomizerSettingsBridge {

    /**
     * @var string
     */
    protected $settings_option;

    /**
     * @var string
     */
    protected $customizer_option;

    /**
     * @param string $settings_option
     * @param string $customizer_option
     */
    public function __construct( $settings_option, $customizer_option ) {
        $this->settings_option   = $settings_option;
        $this->customizer_option = $customizer_option;
    }

    /**
     * @return void
     */
    public function init() {
//        add_action( 'updated_option', function ( $option, $old_value, $value ) {
//            if ( doing_action( 'updated_option' ) ) {
//                return;
//            }
//
//        }, 10, 3 );
    }
}
