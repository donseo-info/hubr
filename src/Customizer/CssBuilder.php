<?php

namespace WPShop\WPCommunity\Customizer;

class CssBuilder {

    const PSEUDO_MEDIA_VARIABLES = '__variables__';

    /**
     * @var bool
     */
    public $strict = true;

    /**
     * @var bool
     */
    public $pretty = false;

    /**
     * @var CssRule[]
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $media_order = [];

    /**
     * @var int
     */
    protected $non_media_sort_order = 10;

    /**
     * CssBuilder constructor.
     */
    public function __construct() {
        $this->media_order[ self::PSEUDO_MEDIA_VARIABLES ] = 5;
    }

    /**
     * @param string $selector
     *
     * @return CssRule
     */
    public function new_rule( $selector ) {
        $this->add_rule( $rule = new CssRule() );
        $rule->set_selector( $selector );

        return $rule;
    }

    /**
     * @param string $media
     * @param string $sort_order
     *
     * @return $this
     */
    public function register_media( $media, $sort_order ) {
        $media = $this->filter_media_expr( $media );

        $this->media_order[ $media ] = $sort_order;

        return $this;
    }

    /**
     * @param CssRule $rule
     *
     * @return $this
     */
    public function add_rule( CssRule $rule ) {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param CssRule $rule
     * @param bool    $reset
     *
     * @return CssRule
     */
    public function duplicate( CssRule $rule, $reset = false ) {
        $item = clone $rule;
        if ( $reset ) {
            $item->reset();
        }
        $this->add_rule( $item );

        return $item;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->build( $this->pretty );
    }

    /**
     * @param bool $pretty
     *
     * @return string
     */
    public function build( $pretty = false ) {
        $rows = [];
        foreach ( $this->rules as $rule ) {
            $media = $this->filter_media_expr( $rule->media );
            if ( $media && ! array_key_exists( $media, $this->media_order ) ) {
                if ( $this->strict ) {
                    _doing_it_wrong(
                        __METHOD__,
                        sprintf( esc_html__( "Trying to build css with not registered media expression '%s'", 'wpcommunity' ), $media ),
                        '0.1.0'
                    );
                }
                continue;
            }
            $rows[ $media ][ $rule->selector ][] = $rule;
        }

        uksort( $rows, function ( $a, $b ) {
            $p1 = array_key_exists( $a, $this->media_order ) ? $this->media_order[ $a ] : $this->non_media_sort_order;
            $p2 = array_key_exists( $b, $this->media_order ) ? $this->media_order[ $b ] : $this->non_media_sort_order;

            return $p1 > $p2 ? 1 : ( $p1 < $p2 ? - 1 : 0 );
        } );

        $space      = $pretty ? ' ' : '';
        $eol        = $pretty ? PHP_EOL : '';
        $indent_lvl = 0;

        $indent = function ( $repeat ) use ( $pretty ) {
            return $pretty ? str_repeat( ' ', $repeat * 2 ) : '';
        };

        $out = '';
        foreach ( $rows as $media => $selectors ) {
            $selectors = $this->build_selectors( $selectors, $pretty, $space, $indent, $indent_lvl, $eol );
            if ( ! trim( $selectors ) ) {
                continue;
            }

            if ( $media && $media !== self::PSEUDO_MEDIA_VARIABLES ) {
                $out .= '@media';
                $out .= $space;
                $out .= $media;
                $out .= $space;
                $out .= '{';
                $out .= $eol;

                $indent_lvl ++;
            }
            $out .= $selectors;
//            foreach ( $selectors as $selector => $items ) {
//                $properties_out = '';
//                foreach ( $items as $item ) {
//                    $properties_out .= $this->build_item_properties( $item, $space, $indent( $indent_lvl + 1 ), $eol );
//                }
//                if ( ! $properties_out ) {
//                    if ( $pretty ) {
//                        $out .= $indent( $indent_lvl );
//                        $out .= $selector;
//                        $out .= $space;
//                        $out .= '{';
//                        $out .= $eol;
//                        $out .= $indent( $indent_lvl + 1 );
//                        $out .= '/* no items found */';
//                        $out .= $eol;
//                        $out .= $indent( $indent_lvl );
//                        $out .= '}';
//                        $out .= $eol;
//                    }
//                    continue;
//                }
//                $out .= $indent( $indent_lvl );
//                $out .= $selector;
//                $out .= $space;
//                $out .= '{';
//                $out .= $eol;
//                $out .= $properties_out;
//                $out .= $indent( $indent_lvl );
//                $out .= '}';
//                $out .= $eol;
//            }
            if ( $media && $media !== self::PSEUDO_MEDIA_VARIABLES ) {
                $out .= '}';
                $out .= $eol;

                $indent_lvl --;
            }
        }

        return $out;
    }

    /**
     * @param array  $selectors
     * @param bool   $pretty
     * @param string $space
     * @param string $indent
     * @param int    $indent_lvl
     * @param string $eol
     *
     * @return string
     */
    protected function build_selectors( $selectors, $pretty, $space, $indent, $indent_lvl, $eol ) {
        $out = '';
        foreach ( $selectors as $selector => $items ) {
            if ( ! $selector ) {
                continue;
            }

            $properties_out = '';
            foreach ( $items as $item ) {
                $properties_out .= $this->build_item_properties( $item, $space, $indent( $indent_lvl + 1 ), $eol );
            }
            if ( ! $properties_out ) {
                if ( $pretty ) {
                    $out .= $indent( $indent_lvl );
                    $out .= $selector;
                    $out .= $space;
                    $out .= '{';
                    $out .= $eol;
                    $out .= $indent( $indent_lvl + 1 );
                    $out .= '/* no items found */';
                    $out .= $eol;
                    $out .= $indent( $indent_lvl );
                    $out .= '}';
                    $out .= $eol;
                }
                continue;
            }
            $out .= $indent( $indent_lvl );
            $out .= $selector;
            $out .= $space;
            $out .= '{';
            $out .= $eol;
            $out .= $properties_out;
            $out .= $indent( $indent_lvl );
            $out .= '}';
            $out .= $eol;
        }

        return $out;
    }

    /**
     * @param CssRule $item
     * @param string  $space
     * @param string  $indent
     * @param string  $eol
     *
     * @return string
     */
    protected function build_item_properties( CssRule $item, $space, $indent, $eol ) {
        $out = '';
        foreach ( $item->properties as $key => $value ) {
            if ( ! $value ) {
                continue;
            }
            $out .= $indent;
            $out .= "$key:";
            $out .= $space;
            $out .= "$value;";
            $out .= $eol;
            if ( array_key_exists( $key, $item->prefixes ) ) {
                $prefixes = $item->prefixes[ $key ];
                foreach ( $prefixes as $prefix => $prefix_value ) {
                    $out .= $indent;
                    $out .= "$prefix:";
                    $out .= $space;
                    $out .= ( $prefix_value ?: $value ) . ';';
                    $out .= $eol;
                }
            }
        }

        return $out;
    }

    /**
     * @param string $media
     *
     * @return string
     */
    protected function filter_media_expr( $media ) {
        if ( $media !== self::PSEUDO_MEDIA_VARIABLES ) {
            $media = str_replace( '@media', '', (string) $media );
            $media = trim( $media );
        }

        return $media;
    }

}
