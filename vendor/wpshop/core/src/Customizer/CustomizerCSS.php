<?php

namespace Wpshop\Core\Customizer;
use Wpshop\Core\Fonts;

/**
 * Class CustomizerCSS
 *
 * @version 1.0.1
 *
 * Changelog
 *
 * 1.0.2    2022-04-28      fix check option and defaults
 * 1.0.1    2019-01-11      add typography properties
 * 1.0.0    2018-10-24      init
 */
class CustomizerCSS {

    private $output = '';
    private $option_name = 'wpshop_core_options';
    private $options = array();
    private $defaults = array();


    public function __construct( $args = array() ) {
        if ( ! empty( $args['defaults'] ) ) {
            $this->defaults = $args['defaults'];
        }
        if ( ! empty( $args['theme_slug'] ) ) {
            $this->option_name = $args['theme_slug'] . '_options';
        }
        $this->options = get_option( $this->option_name, array() );
    }


    public function add( $selector = '', $property = '', $media = '' ) {

        if ( ! empty( $selector ) && ! empty( $property ) ) {

            // если 1 свойство
            if ( ! is_array( $property[0] ) ) {

                $properties = $this->make_properties( $property );

            } else {

                $properties = '';
                foreach ( $property as $item ) {
                    if ( ! empty( $properties ) ) $properties .= ';';
                    $properties .= $this->make_properties( $item );
                }

            }

            if ( ! empty( $properties ) ) {

                if ( ! empty( $media ) ) $this->output .= '@media '. $media .'{';
                $this->output .= $selector . '{' . $properties . '}';
                if ( ! empty( $media ) ) $this->output .= '}';

            }
        }
    }


    public function make_properties( $property ) {

        $properties = '';

        // если есть опция
        if ( ! empty( $property[1] ) ) {

            $class_fonts = new Fonts();

            // если шрифт старый способ
            if ( mb_strpos( $property[0], 'font-family' ) !== false ) {
                if ( ! empty( $this->options[$property[1]] ) ) {

                    $properties = sprintf( $property[0], $class_fonts->get_font_family( $this->options[ $property[1] ] ) );

                    return $properties;
                }
            }

            // если шрифт кастомный контрол Typography
            if ( $property[0] == 'typography' ) {
                if ( ! empty( $this->options[$property[1]] ) ) {

                    // получаем опции
                    $font_options = $this->options[$property[1]];
                    if ( ! empty( $font_options ) ) {
                        $font_options = json_decode( $font_options, true );
                    }

                    // получаем дефолтные опции
                    $font_options_default = $this->defaults[$property[1]];
                    if ( ! empty( $font_options_default ) ) {
                        $font_options_default = json_decode( $font_options_default, true );
                    }

                    $unit = ( ! empty( $font_options['unit'] ) ) ? $font_options['unit'] : 'px';

                    // TODO: проверить дефолтные значения

                    $properties = '';
                    if ( ! empty( $font_options['font-family'] ) && $font_options['font-family'] != $font_options_default['font-family'] ) {
                        $properties .= 'font-family:' .  $class_fonts->get_font_family( $font_options['font-family'] ) . ';';
                    }
                    if ( ! empty( $font_options['font-size'] ) && $font_options['font-size'] != $font_options_default['font-size'] ) {
                        $properties .= 'font-size:' . $font_options['font-size'] . $unit . ';';
                    }
                    if ( ! empty( $font_options['line-height'] ) && $font_options['line-height'] != $font_options_default['line-height'] ) {
                        $properties .= 'line-height:' . $font_options['line-height'] . ';';
                    }
                    if ( ! empty( $font_options['color'] ) && $font_options['color'] != $font_options_default['color'] ) {
                        $properties .= 'color:' . $font_options['color'] . ';';
                    }
                    if ( ! empty( $font_options['font-style'] ) && stristr( $font_options['font-style'], 'bold' ) ) {
                        $properties .= 'font-weight:bold;';
                    }
                    if ( ! empty( $font_options['font-style'] ) && stristr( $font_options['font-style'], 'italic' ) ) {
                        $properties .= 'font-style:italic;';
                    }
                    if ( ! empty( $font_options['font-style'] ) && stristr( $font_options['font-style'], 'underline' ) ) {
                        $properties .= 'text-decoration:underline;';
                    }
                    if ( ! empty( $font_options['font-style'] ) && stristr( $font_options['font-style'], 'uppercase' ) ) {
                        $properties .= 'text-transform:uppercase;';
                    }

                    return $properties;

                }
            }

            // проверяем есть ли настройки и не равны ли дефолтным
            if ( array_key_exists( $property[1], (array) $this->options ) ) {
                if ( ! array_key_exists( $property[1], $this->defaults ) || $this->options[ $property[1] ] != $this->defaults[ $property[1] ] ) {
                    $properties = sprintf( $property[0], $this->options[ $property[1] ] );
                }
            }
        } else {
            $properties = $property[0];
        }

        return $properties;
    }


    public function output() {
        return $this->output;
    }

}
