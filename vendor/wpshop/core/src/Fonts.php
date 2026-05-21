<?php

namespace Wpshop\Core;

/**
 * Class Fonts
 *
 * Changelog
 *
 * 1.0.5    add filter wpshop_fonts_preload_fonts
 * 1.0.4    add filter wpshop_fonts_enqueue_fonts
 * 1.0.3    add preloading_fonts() method
 * 1.0.2    add Rubik font, add display=swap to url
 * 1.0.1    add function get_enqueue_link and change fonts array
 * 1.0.0    init
 */
class Fonts {

    protected $options;
    public $fonts = array();

    /**
     * @var array
     */
    protected $preload_fonts_params = [];

    /**
     * Fonts constructor.
     *
     * @param ThemeOptions $options
     */
    public function __construct( ThemeOptions $options = null ) {

        $this->options = $options;

        add_action( 'wp_head', [ $this, 'wp_head_preload_fonts' ] );

        /**
         * family sans-serif default
         */
        $this->fonts = apply_filters( 'wpshop_fonts_list', array(
            'arial'             => array(
                'name'          => 'Arial',
            ),
            'amatic_sc'            => array(
                'name'          => 'Amatic SC',
                'url'           => 'Amatic+SC',
                'weight'        => '400,700',
            ),
            'bad_script'        => array(
                'name'          => 'Bad Script',
                'url'           => 'Bad+Script',
            ),
            'caveat'            => array(
                'name'          => 'Caveat',
                'url'           => 'Caveat',
                'weight'        => '400,700',
            ),
            'exo_2'             => array(
                'name'          => 'Exo 2',
                'url'           => 'Exo+2',
                'weight'        => '400,400i,700',
            ),
            'fira_sanscondensed'=> array(
                'name'          => 'Fira Sans Condensed',
                'url'           => 'Fira+Sans+Condensed',
                'weight'        => '400,400i,700',
            ),
            'kurale'            => array(
                'name'          => 'Kurale',
                'url'           => 'Kurale',
            ),
            'lobster'           => array(
                'name'          => 'Lobster',
                'url'           => 'Lobster',
            ),
            'lora'              => array(
                'name'          => 'Lora',
                'url'           => 'Lora',
                'weight'        => '400,400i,700',
            ),
            'merriweather'      => array(
                'name'          => 'Merriweather',
                'url'           => 'Merriweather',
                'weight'        => '400,400i,700',
                'family'        => 'serif',
            ),
            'montserrat'        => array(
                'name'          => 'Montserrat',
                'url'           => 'Montserrat',
                'weight'        => '400,400i,700',
            ),
            'open_sans'         => array(
                'name'          => 'Open Sans',
                'url'           => 'Open+Sans',
                'weight'        => '400,400i,700',
            ),
            'open_sans_condensed'=> array(
                'name'          => 'Open Sans Condensed',
                'url'           => 'Open+Sans+Condensed',
                'weight'        => '300,300i,700',
            ),
            'oswald'            => array(
                'name'          => 'Oswald',
                'url'           => 'Oswald',
                'weight'        => '400,700',
            ),
            'pacifico'            => array(
                'name'          => 'Pacifico',
                'url'           => 'Pacifico',
            ),
            'pt_sans'           => array(
                'name'          => 'PT Sans',
                'url'           => 'PT+Sans',
                'weight'        => '400,400i,700',
            ),
            'pt_serif'          => array(
                'name'          => 'PT Serif',
                'url'           => 'PT+Serif',
                'weight'        => '400,400i,700',
            ),
            'roboto'            => array(
                'name'          => 'Roboto',
                'url'           => 'Roboto',
                'weight'        => '400,400i,700',
            ),
            'roboto_condensed'  => array(
                'name'          => 'Roboto Condensed',
                'url'           => 'Roboto+Condensed',
                'weight'        => '400,400i,700',
            ),
            'roboto_slab'       => array(
                'name'          => 'Roboto Slab',
                'url'           => 'Roboto+Slab',
                'weight'        => '400,700',
                'family'        => 'serif',
            ),
            'rubik'             => array(
                'name'          => 'Rubik',
                'url'           => 'Rubik',
                'weight'        => '400,400i,500,700',
            ),
            'times_serif'       => array(
                'name'          => 'Times New Roman',
                'family'        => 'serif',
            ),
            'tinos'             => array(
                'name'          => 'Tinos',
                'url'           => 'Tinos',
                'weight'        => '400,400i,700',
            ),
            'ubuntu'            => array(
                'name'          => 'Ubuntu',
                'url'           => 'Ubuntu',
                'weight'        => '400,400i,700',
            ),
            'ubuntu_condensed'  => array(
                'name'          => 'Ubuntu Condensed',
                'url'           => 'Ubuntu+Condensed',
            ),
            'yanone_kaffeesatz' => array(
                'name'          => 'Yanone Kaffeesatz',
                'url'           => 'Yanone+Kaffeesatz',
                'weight'        => '400,700',
            ),
        ) );

    }


    /**
     * @return array
     */
    public function get_fonts() {
        return $this->fonts;
    }

    /**
     * @return array
     */
    public function get_fonts_key_value() {
        $fonts_list = array();
        foreach ( $this->fonts as $key => $val ) {
            $fonts_list[$key] = $val['name'];
        }
        return $fonts_list;
    }


    /**
     * Get google enqueue link
     * Возвращает ссылку для добавления шрифтов
     * Удаляет дубли
     *
     * @param array $fonts
     *
     * @return string
     */
    public function get_enqueue_link( $fonts = array() ) {

        $fonts = apply_filters( 'wpshop_fonts_enqueue_fonts', $fonts );

        if ( ! empty( $fonts ) ) {

            // удаляем дубли
            $fonts = array_unique( $fonts );

            $list = array();

            foreach ( $fonts as $font ) {
                if ( isset( $this->fonts[$font]['url'] ) ) {
                    $font_name = $this->fonts[$font]['url'];

                    // если есть жирность шрифта
                    if ( ! empty( $this->fonts[$font]['weight'] ) ) {
                        $font_name .= ':' . $this->fonts[$font]['weight'];
                    }
                    $list[] = $font_name;
                }
            }

            if ( ! empty( $list ) ) {
                $fonts_enqueue = implode( '|', $list );
                $fonts_enqueue = 'https://fonts.googleapis.com/css?family=' . $fonts_enqueue . '&amp;subset=cyrillic&amp;display=swap';

                return $fonts_enqueue;
            }
        }
        return '';

    }


    public function get_font_family( $font ) {
        if ( ! empty( $this->fonts[$font] ) ) {
            if ( ! empty( $this->fonts[$font]['family'] ) && $this->fonts[$font]['family'] == 'serif' ) {
                return '"'. $this->fonts[$font]['name'] .'" ,"Georgia", "Times New Roman", "Bitstream Charter", "Times", serif';
            } else {
                return '"'. $this->fonts[$font]['name'] .'" ,"Helvetica Neue", Helvetica, Arial, sans-serif';
            }
        }
        return '';
    }

    /**
     * @param string $url
     * @param string $as
     *
     * @return void
     */
    public function preloading_fonts( $url, $as = 'font' ) {
        $this->preload_fonts_params[] = [ $url, $as ];
    }

    /**
     * @return void
     */
    public function wp_head_preload_fonts() {
        $this->preload_fonts_params = apply_filters( 'wpshop_fonts_preload_fonts', $this->preload_fonts_params );

        foreach ( $this->preload_fonts_params as $params ) {
            list( $url, $as ) = $params;
            echo '<link rel="preload" href="' . $url . '" as="' . $as . '" crossorigin>';
        }
    }
}
