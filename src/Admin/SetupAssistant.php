<?php

namespace WPShop\WPCommunity\Admin;

class SetupAssistant {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    /**
     * @return array[]
     */
    public function get_steps() {
        $steps = [
            [
                'title'    => __( 'Create Pages', 'wpcommunity' ),
                'template' => 'pages',
            ],
            [
                'title'    => __( 'Create Menu', 'wpcommunity' ),
                'template' => 'menu',
            ],
            [
                'title'    => __( 'Create Widgets', 'wpcommunity' ),
                'template' => 'widgets',
            ],
        ];

        if ( ! $this->settings->verify() ) {
            $steps = array_map( function ( $step ) {
                $step['disabled'] = true;

                return $step;
            }, $steps );
            array_unshift( $steps, [
                'title'    => __( 'Activation', 'wpcommunity' ),
                'template' => 'activation',
            ] );
        }

        return $steps;
    }

    /**
     * @param int $idx
     *
     * @return bool
     */
    public function has_next( $idx ) {
        $steps = $this->get_steps();

        return isset( $steps[ $idx + 1 ] );
    }

    /**
     * @param int  $idx
     * @param bool $default
     *
     * @return false
     */
    public function is_disabled( $idx, $default = false ) {
        $steps = $this->get_steps();
        if ( isset( $steps[ $idx ]['disabled'] ) ) {
            if ( is_callable( $steps[ $idx ]['disabled'] ) ) {
                return call_user_func( $steps[ $idx ]['disabled'], $idx, $steps );
            }

            return $steps[ $idx ]['disabled'];
        }

        return $default;
    }
}
