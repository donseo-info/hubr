<?php

namespace WPShop\WPCommunity\Layout;


use WPShop\WPCommunity\Customizer\Customizer;
use function WPShop\WPCommunity\is_post_element_hidden;

class Sidebar {

    /**
     * @var Customizer
     */
    protected $customizer;

    /**
     * @var array
     */
    public $_state = [];

    /**
     * @param Customizer $customizer
     */
    public function __construct( Customizer $customizer ) {
        $this->customizer = $customizer;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp', [ $this, '_init_sidebar_state' ] );
    }

    /**
     * @return void
     */
    public function _init_sidebar_state() {
        $sidebars = [
            'sidebar-1',
            'sidebar-2',
        ];

        foreach ( $sidebars as $name ) {
            $this->_state[ $name ] = $this->init_hidden_state( $name );
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function init_hidden_state( $name ) {
        $hidden = false;

        switch ( $name ) {
            case 'sidebar-1';
                $hidden = is_singular( 'post' ) && $this->customizer->get_option( 'post.hide_sidebar_1' ) ||
                          ! ( is_active_sidebar( 'sidebar-top' ) || is_active_sidebar( 'sidebar-bottom' ) );
                break;
            case 'sidebar-2';
                $hidden = is_singular( 'post' ) && $this->customizer->get_option( 'post.hide_sidebar_2' ) ||
                          ! is_active_sidebar( $name );
                break;
            default:
                break;
        }

        if ( is_singular( [ 'post', 'page' ] ) &&
             in_array( $name, [ 'sidebar-1', 'sidebar-2' ] )
        ) {
            if ( is_post_element_hidden( $name ) ) {
                $hidden = true;
            }
        }

        /**
         * @since 1.0
         */
        $hidden = (bool) apply_filters( 'wpcommunity/sidebar/is_hidden', $hidden, $name );

        return $hidden;
    }

    /**
     * @param string $name
     *
     * @return false
     */
    public function is_sidebar_hidden( $name ) {
        return array_key_exists( $name, $this->_state ) ? $this->_state[ $name ] : false;
    }

}
