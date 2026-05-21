<?php

namespace Wpshop\Core;
use Wpshop\SimpleHtmlDom\SimpleHtmlDom;

/**
 * Class TableOfContents
 *
 * @version 1.0.4
 *
 * Changelog
 *
 * 1.0.8    2021-11-10    add method get_toc_for_widget
 * 1.0.7    2021-10-25    add filter wpshop_toc_minimum_headers
 * 1.0.6    2021-03-18    escape dollar sign in regexp
 * 1.0.5    2019-03-17    change PHP Simple DOM Parser to WPShop version
 * 1.0.4    2018-09-27    add options, remove hooks, add hide_element check
 * 1.0.3    2018-09-07    add custom text with title attr
 * 1.0.2    2018-09-07    add composer HtmlDomParser loading
 * 1.0.1    2018-09-07    add filter wpshop_toc_open
 * 1.0.0    2018-09-07    init
 */
class TableOfContents {

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

    private $list = array();

    private $in_single = true;
    private $in_page = false;

    public function __construct( ThemeOptions $options, Helper $helper = null, Core $core = null ) {
        $this->options = $options;
        $this->helper = $helper ?: new Helper();
        $this->core = $core;

        if (!$core) {
            global $wpshop_core;
            $this->core = $wpshop_core;;
        }

    }

    public function init() {
        add_filter( 'the_content', array( $this, 'add_toc' ) );
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function get_toc_for_widget( $content ) {
        $this->get_tags( $content, apply_filters( 'wpshop_toc_headers', array( 'h1', 'h2', 'h3', 'h4' ) ) );

        return $this->create_toc();
    }

    public function add_toc( $content ) {

        if ( ! empty( $GLOBALS['wp_current_filter'] ) && in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
            return $content;
        }

        if ( ! is_page() && ! is_single() )
            return $content;

        $this->in_single = apply_filters( 'wpshop_toc_in_single', true );
        $this->in_page = apply_filters( 'wpshop_toc_in_page', false );

        if ( is_single() && ! $this->in_single )
            return $content;

        if ( is_page() && ! $this->in_page )
            return $content;

        if ( ! $this->core->is_show_element( 'toc' ) )
            return $content;


        $toc = '';
        $toc_info = '';


        /**
         * Generate toc and set headers ids
         */
        $content = $this->get_tags( $content, apply_filters( 'wpshop_toc_headers', array( 'h1', 'h2', 'h3', 'h4' ) ) );
        $toc = $this->create_toc();


        /**
         * If empty - return
         */
        if ( empty( $toc ) ) {
            $toc_info .= '<!-- toc empty -->';
            return $content . $toc_info;
        }


        $toc_place = apply_filters( 'wpshop_toc_place', 'before_header' ); // before_content


        /**
         * If shortcode exist in
         */
        if ( preg_match( '/\[toc\]/ui', $content ) ) {
            $content = preg_replace( '/\[toc\]/ui', $toc, $content );
            $toc_info .= '<!-- toc shortcode -->';
            return $content . $toc_info;
        }


        /**
         * Before header
         */
        if ( 'before_header' == $toc_place ) {
            $toc = str_replace( '$', '\$', $toc );
            $content = preg_replace( '/(<h([1-6]{1})[^>]*>)/msuU', $toc . '${1}' , $content, 1 );
            return $content . $toc_info;
        }

        return $toc . $content . $toc_info;
    }


    private function create_toc() {

        $out = '';

        if ( empty( $this->list ) ) return '';

        $max_header = 6;
        $current_level = 1;

        foreach ( $this->list as $item ) {
            if ( $item['header'] < $max_header ) $max_header = $item['header'];
        }

        $is_open = apply_filters( 'wpshop_toc_open', true );

        if ( ! $is_open || ( ! empty( $_COOKIE['wpshop_toc_hide'] ) && $_COOKIE['wpshop_toc_hide'] == 'hide' ) ) {
            $toc_class = '';
            $toc_style = ' style="display:none;"';
        } else {
            $toc_class = ' open';
            $toc_style = '';
        }

        $is_noindex = apply_filters( 'wpshop_toc_noindex', false );

        $out .= '<div class="table-of-contents'. $toc_class .'">';
        if ( $is_noindex ) $out .= '<!--noindex-->';
        $out .= '<div class="table-of-contents__header"><span class="table-of-contents__hide js-table-of-contents-hide">' . apply_filters( 'wpshop_toc_header', __( 'Contents', $this->options->text_domain ) ) . '</span></div>';
        $out .= '<ol class="table-of-contents__list js-table-of-contents-list"'. $toc_style .'>' . PHP_EOL;

        foreach ( $this->list as $item ) {

            $slug = ( ! empty( $item['id'] ) ) ? $item['id'] : $item['slug'] ;

            $level = $item['header'] - $max_header + 1;
            $out .= '<li class="level-'. $level .'"><a href="#' . $slug . '">' . $item['text'] . '</a></li>';

        }

        $out .= '</ol>';
        if ( $is_noindex ) $out .= '<!--/noindex-->';
        $out .= '</div>';


        return $out;

    }



    private function get_tags( $content, $tags = array( 'h1', 'h2', 'h3' ) ) {

        $slugs = array();
        $list = array();
        $n = 0;

        if ( empty( $content ) ) return $content;

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
        $headers = apply_filters( 'wpshop_toc_header_elements', $headers );

        /**
         * If minimum headers
         */
        if ( count($headers) < apply_filters( 'wpshop_toc_minimum_headers', 3 ) ) {
            return $content;
        }


        foreach ( $headers as $header ) {

            /**
             * If empty tag - skip
             */
            if ( empty( $header->plaintext ) ) continue;


            /**
             * Add ti list
             */
            $to_list = array(
                'header'    => mb_substr( $header->tag, 1 ),
                'tag'       => $header->tag,
                'text'      => $header->plaintext,
            );



            /**
             * If title exists - set text and remove it
             */
            if ( ! empty( $header->title ) ) {
                $to_list['text'] = $header->title;
                if ( apply_filters( 'wpshop_toc_remove_title', true ) ) {
                    $header->title = null;
                }
            }


            /**
             * Transliterate
             */
            if ( class_exists( 'Wpshop\Core\Transliteration' ) ) {

                $wpshop_transliteration = new Transliteration();
                $transliterate = $wpshop_transliteration->transliterate( $to_list['text'] );
                if ( ! empty( $transliterate ) ) {
                    $to_list['slug'] = $transliterate;
                } else {
                    $to_list['slug'] = apply_filters( 'wpshop_toc_prefix', 'p' );
                }

            } else {

                $string = str_replace( ' ', '-', $to_list['text'] );
                $to_list['slug'] = $string;

            }


            /**
             * Maximum length
             */
            $max_length = apply_filters( 'wpshop_toc_max_length', 40 );
            if ( mb_strlen( $to_list['slug'] ) > $max_length ) {
                $slug_spaces = $this->helper->substring_by_word( $to_list['slug'], $max_length, '-' );
                $slug_spaces = str_replace( ' ', '-', $slug_spaces );
                $to_list['slug'] = $slug_spaces;
            }


            /**
             * Check duplicate
             */
            if ( array_key_exists( $to_list['slug'], $slugs ) ) {

                $slugs[ $to_list['slug'] ] = $slugs[ $to_list['slug'] ] + 1;
                $to_list['slug'] = $to_list['slug'] . '-' . $slugs[ $to_list['slug'] ];

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
