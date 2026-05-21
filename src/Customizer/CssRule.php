<?php

namespace WPShop\WPCommunity\Customizer;

class CssRule {
    /**
     * @var string|null
     */
    public $selector;

    /**
     * Associative map
     *
     * @var array
     */
    public $properties = [];

    /**
     * Associative map
     *
     * @var array
     */
    public $prefixes = [];

    /**
     * @var string|null
     */
    public $media;

    /**
     * @return $this
     */
    public function reset() {
        $this->properties = [];
        $this->prefixes   = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function reset_media() {
        $this->media = null;

        return $this;
    }

    /**
     * @param string $selector
     *
     * @return $this
     */
    public function set_selector( $selector ) {
        $this->selector = $selector;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_media( $value ) {
        $this->media = $value;

        return $this;
    }

    /**
     * @param string $property
     * @param string $value
     *
     * @return $this
     */
    public function add_property( $property, $value ) {
        $this->properties[ $property ] = $value;
        $this->setup_prefixes( $property, $value );

        return $this;
    }

    /**
     * @param string $property
     *
     * @return mixed|null
     */
    public function get_property( $property ) {
        return $this->properties[ $property ] ?? null;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function add_properties( array $properties ) {
        foreach ( $properties as $property => $value ) {
            $this->add_property( $property, $value );
        }

        return $this;
    }

    /**
     * @param string $property
     * @param string $value
     *
     * @return void
     */
    protected function setup_prefixes( $property, $value ) {
        switch ( $property ) {
            case 'order':
                $this->prefixes[ $property ]['-ms-flex-order'] = null;
                break;
            case 'flex':
                $this->prefixes[ $property ]['-ms-flex'] = null;
                break;
            case 'align-items':
                $this->prefixes[ $property ]['-webkit-box-align'] = null;
                $this->prefixes[ $property ]['-ms-flex-align']    = null;
                break;
            case 'justify-content':
                if ( $value == 'flex-start' ) {
                    $this->prefixes[ $property ]['-webkit-box-pack'] = 'start';
                    $this->prefixes[ $property ]['-ms-flex-pack']    = 'start';
                } elseif ( $value == 'flex-end' ) {
                    $this->prefixes[ $property ]['-webkit-box-pack'] = 'end';
                    $this->prefixes[ $property ]['-ms-flex-pack']    = 'end';
                } elseif ( $value == 'space-between' ) {
                    $this->prefixes[ $property ]['-webkit-box-pack'] = 'justify';
                    $this->prefixes[ $property ]['-ms-flex-pack']    = 'justify';
                } elseif ( $value == 'space-around' ) {
                    $this->prefixes[ $property ]['-ms-flex-pack'] = 'distribute';
                } else {
                    $this->prefixes[ $property ]['-webkit-box-pack'] = null;
                    $this->prefixes[ $property ]['-ms-flex-pack']    = null;
                }
                break;
            case 'user-select':
                $this->prefixes[ $property ]['-webkit-touch-callout'] = null;
                $this->prefixes[ $property ]['-webkit-user-select']   = null;
                $this->prefixes[ $property ]['-khtml-user-select']    = null;
                $this->prefixes[ $property ]['-moz-user-select']      = null;
                $this->prefixes[ $property ]['-ms-user-select']       = null;
                break;
            default:
                break;
        }

        /**
         * Allows to modify prefixes for css rules
         *
         * [ru] Позволяет изменить префиксы для css правил
         *
         * @since 1.0
         */
        do_action( 'wpcommunity/css_rule/setup_prefixes', $this, $property, $value );
    }
}
