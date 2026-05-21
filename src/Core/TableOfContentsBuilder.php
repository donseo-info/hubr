<?php

namespace WPShop\WPCommunity\Core;

use Wpshop\Core\Core;
use Wpshop\Core\Helper;
use Wpshop\Core\ThemeOptions;
use Wpshop\Core\Transliteration;
use Wpshop\SimpleHtmlDom\SimpleHtmlDom;

class TableOfContentsBuilder {
    /**
     * @var ThemeOptions
     */
    protected $options;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Core
     */
    protected $core;

    protected $list = [];

    protected $in_single = true;
    protected $in_page = false;

    public function __construct( ThemeOptions $options, Helper $helper = null, Core $core = null ) {
        $this->options = $options;
        $this->helper  = $helper ?: new Helper();
        $this->core    = $core;

        if ( ! $core ) {
            global $wpshop_core;
            $this->core = $wpshop_core;;
        }

    }

    /**
     * @return void
     */
    public function init() {
        add_filter( 'the_content', [ $this, 'add_toc' ] );
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function get_toc_for_widget( $content ) {
        $this->get_tags( $content, apply_filters( 'wpcommunity/toc/header', [ 'h1', 'h2', 'h3', 'h4' ] ) );

        return $this->create_toc();
    }

    public function add_toc( $content ) {

        if ( ! empty( $GLOBALS['wp_current_filter'] ) && in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
            return $content;
        }

        if ( ! is_page() && ! is_single() ) {
            return $content;
        }

        $this->in_single = apply_filters( 'wpcommunity/toc/single', true );
        $this->in_page   = apply_filters( 'wpcommunity/toc/page', false );

        if ( is_single() && ! $this->in_single ) {
            return $content;
        }

        if ( is_page() && ! $this->in_page ) {
            return $content;
        }

        if ( ! $this->core->is_show_element( 'toc' ) ) {
            return $content;
        }


        $toc      = '';
        $toc_info = '';


        /**
         * Generate toc and set headers ids
         */
        $content = $this->get_tags( $content, apply_filters( 'wpcommunity/toc/headers', [ 'h1', 'h2', 'h3', 'h4' ] ) );
        $toc     = $this->create_toc();


        /**
         * If empty - return
         */
        if ( empty( $toc ) ) {
            $toc_info .= '<!-- toc empty -->';

            return $content . $toc_info;
        }


        $toc_place = apply_filters( 'wpcommunity/toc/place', 'before_header' ); // before_content


        /**
         * If shortcode exist in
         */
        if ( preg_match( '/\[toc\]/ui', $content ) ) {
            $content  = preg_replace( '/\[toc\]/ui', $toc, $content );
            $toc_info .= '<!-- toc shortcode -->';

            return $content . $toc_info;
        }


        /**
         * Before header
         */
        if ( 'before_header' == $toc_place ) {
            $toc     = str_replace( '$', '\$', $toc );
            $content = preg_replace( '/(<h([1-6]{1})[^>]*>)/msuU', $toc . '${1}', $content, 1, $count );

            return $content . $toc_info;
        }

        return $toc . $content . $toc_info;
    }


    protected function create_toc() {

        $out = '';

        if ( empty( $this->list ) ) {
            return '';
        }

        $max_header    = 6;
        $current_level = 1;

        foreach ( $this->list as $item ) {
            if ( $item['header'] < $max_header ) {
                $max_header = $item['header'];
            }
        }

        /**
         * Allows to change initial open state
         *
         * [ru] Позволяет поменять начальное состояние
         *
         * @since 1.1
         */
        $is_open = apply_filters( 'wpcommunity/toc/open', true );

        if ( ! $is_open || ( ! empty( $_COOKIE['wpsc_toc_hide'] ) && $_COOKIE['wpsc_toc_hide'] == 'hide' ) ) {
            $toc_class = '';
            $toc_style = ' style="display:none;"';
        } else {
            $toc_class = ' open';
            $toc_style = '';
        }

        /**
         * Allows to enable noindex wrap
         *
         * [ru] Позволяет включить оборачивание в noindex
         *
         * @since 1.1
         */
        $is_noindex = apply_filters( 'wpcommunity/toc/noindex', false );

        /**
         * Allows to modify toc title
         *
         * [ru] Позволяет поменять заголовок содержания
         *
         * @since 1.1
         */
        $title = apply_filters( 'wpcommunity/toc/title', __( 'Contents', $this->options->text_domain ) );

        $out .= '<div class="table-of-contents' . $toc_class . '">';
        if ( $is_noindex ) {
            $out .= '<!--noindex-->';
        }
        $out .= '<div class="table-of-contents__header"><span class="table-of-contents__hide js-table-of-contents-hide">' .
                $title .
                '<span class="table-of-contents-chevrons"><svg width=".75rem" height=".75rem" class="chevron-down"><use xlink:href="#ico-vote-minus"></use></svg><svg width=".75rem" height=".75rem" class="chevron-up"><use xlink:href="#ico-vote-plus"></use></svg></span>' .
                '</span></div>';
        $out .= '<ol class="table-of-contents__list js-table-of-contents-list"' . $toc_style . '>' . PHP_EOL;

        foreach ( $this->list as $item ) {

            $slug = ( ! empty( $item['id'] ) ) ? $item['id'] : $item['slug'];

            $level = $item['header'] - $max_header + 1;
            $out   .= '<li class="level-' . $level . '"><a href="#' . $slug . '">' . $item['text'] . '</a></li>';

        }

        $out .= '</ol>';
        if ( $is_noindex ) {
            $out .= '<!--/noindex-->';
        }
        $out .= '</div>';


        return $out;

    }


    protected function get_tags( $content, $tags = [ 'h1', 'h2', 'h3' ] ) {

        $slugs = [];
        $list  = [];
        $n     = 0;

        if ( empty( $content ) ) {
            return $content;
        }

        /**
         * PHP Simple HTML DOM Parser
         */
        //if ( ! function_exists( 'str_get_html' ) ) {
        //require get_template_directory() . '/inc/assets/simple_html_dom.php';
        //}
        //$html = str_get_html( $content, true, true, DEFAULT_TARGET_CHARSET, false );

        $html = SimpleHtmlDom::str_get_html( $content, true, true, DEFAULT_TARGET_CHARSET, false );

        if ( ! $html ) {
            return $content;
        }

        /** @var \simplehtmldom_source\simple_html_dom_node[] $headers */
        $headers = $html->find( implode( ', ', $tags ) );

        /**
         * Allows to modify found headers
         *
         * [ru] Позволяет модифицировать найденные заголовки
         *
         * @since 1.1
         */
        $headers = apply_filters( 'wpcommunity/toc/header_elements', $headers );

        /**
         * Allows to change minimum required headers
         *
         * [ru] Позволяет поменять минимальное число заголовков
         *
         * @since 1.1
         */
        $minimum_headers = apply_filters( 'wpcommunity/toc/minimum_headers', 3 );

        /**
         * If minimum headers
         */
        if ( count( $headers ) < $minimum_headers ) {
            return $content;
        }


        foreach ( $headers as $header ) {

            /**
             * If empty tag - skip
             */
            if ( empty( $header->plaintext ) ) {
                continue;
            }


            /**
             * Add ti list
             */
            $to_list = [
                'header' => mb_substr( $header->tag, 1 ),
                'tag'    => $header->tag,
                'text'   => $header->plaintext,
            ];


            /**
             * If title exists - set text and remove it
             */
            if ( ! empty( $header->title ) ) {
                $to_list['text'] = $header->title;

                /**
                 * Allows to prevent remove title
                 *
                 * [ru] Позволяет предотвратить удаление заголовка
                 *
                 * @since 1.0
                 */
                $remove_title = apply_filters( 'wpcommunity/toc/remove_title', true );

                if ( $remove_title ) {
                    $header->title = null;
                }
            }


            /**
             * Transliterate
             */
            if ( class_exists( 'Wpshop\Core\Transliteration' ) ) {

                $wpshop_transliteration = new Transliteration();
                $transliterate          = $wpshop_transliteration->transliterate( $to_list['text'] );
                if ( ! empty( $transliterate ) ) {
                    $to_list['slug'] = $transliterate;
                } else {
                    /**
                     * Allows to modify slug prefix
                     *
                     * [ru] Позволяет поменять префикс слага
                     *
                     * @since 1.1
                     */
                    $to_list['slug'] = apply_filters( 'wpcommunity/toc/slug_prefix', 'p' );
                }

            } else {

                $string          = str_replace( ' ', '-', $to_list['text'] );
                $to_list['slug'] = $string;

            }


            /**
             * Allows to modify max slug length
             *
             * [ru] Позволяет поменять максимальную длину слага
             *
             * @since 1.1
             */
            $max_length = apply_filters( 'wpcommunity/toc/max_slug_length', 40 );
            if ( mb_strlen( $to_list['slug'] ) > $max_length ) {
                $slug_spaces     = $this->helper->substring_by_word( $to_list['slug'], $max_length, '-' );
                $slug_spaces     = str_replace( ' ', '-', $slug_spaces );
                $to_list['slug'] = $slug_spaces;
            }


            /**
             * Check duplicate
             */
            if ( array_key_exists( $to_list['slug'], $slugs ) ) {

                $slugs[ $to_list['slug'] ] = $slugs[ $to_list['slug'] ] + 1;
                $to_list['slug']           = $to_list['slug'] . '-' . $slugs[ $to_list['slug'] ];

            } else {
                $slugs[ $to_list['slug'] ] = 1;
            }


            /**
             * If ID exists
             */
            if ( ! empty( $header->id ) ) {
                $to_list['id'] = $header->id;
            } else {
                $header->id = $to_list['slug'];
            }


            $list[] = $to_list;

        }

        $this->list = $list;

        if ( isset( $html ) && ! empty( $html ) ) {
            return $html;
        } else {
            return $content;
        }

        /*echo '<pre>';
        print_r( $list );
        echo '</pre>';*/

    }
}
