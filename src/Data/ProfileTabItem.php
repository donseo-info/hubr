<?php

namespace WPShop\WPCommunity\Data;

/**
 * @property string          $name
 * @property string|callable $nav
 * @property string|callable $content
 * @property bool|callable   $is_visible
 * @property int|null        $sort_order
 */
class ProfileTabItem {

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct( array $data ) {
        $this->data = $data;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get( $name ) {
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set( $name, $value ) {
        if ( array_key_exists( $name, $this->data ) ) {
            $this->data[ $name ] = $value;
        }
    }

    /**
     * @return bool
     */
    public function is_visible() {
        if ( is_callable( $this->is_visible ) ) {
            return call_user_func( $this->is_visible );
        }

        return $this->is_visible;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function the_nav() {
        $this->_call_tab_part( 'nav' );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function the_content() {
        $this->_call_tab_part( 'content' );
    }

    /**
     * @param string $property
     *
     * @return void
     * @throws \Exception
     */
    protected function _call_tab_part( $property ) {
        if ( is_callable( $this->{$property} ) ) {
            call_user_func( $this->{$property} );
        } else {
            echo $this->{$property};
        }
    }
}
