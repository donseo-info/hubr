<?php

namespace Wpshop\Core;

/**
 * Class Template
 *
 * 2025-03-26       add method jsonld_image(), replace schema.org with ld+json for images
 * 2023-02-09       fix microformat_image when after height no space
 * 2022-07-07       add filter wpshop_microdata_publisher_output
 * 2021-05-19       catch wp_error in get_term_link
 * 2021-01-22       add filter wpshop_core_remove_current_links:preserve_classes
 * 2020-04-13       add filter wpshop_core_views_counter__meta_field
 * 2019-04-22       add body_class_customizer
 * 2018-09-09       add get_views
 * 2018-09-08       init
 */
class Template {


    protected $options;


    public function __construct( ThemeOptions $options ) {

        $this->options = $options;

    }

    public function init( $args = array() ) {

        if ( in_array( 'remove_hentry', $args ) )
            add_filter( 'post_class', array( $this, 'remove_hentry_from_post_classes' ) );

        if ( in_array( 'remove_current_links_from_menu', $args ) )
            add_filter( 'wp_nav_menu', array( $this, 'remove_current_links_from_menu' ), PHP_INT_MAX, 2 );

        if ( in_array( 'remove_h_tag_from_navigation', $args ) )
            add_filter( 'navigation_markup_template', array( $this, 'change_navigation_markup_template' ), 10, 2 );

        if ( in_array( 'remove_label_archive_title', $args ) )
            add_filter( 'get_the_archive_title', array( $this, 'get_the_archive_title_change' ) );

        if ( in_array( 'remove_yoast_breadcrumbs_last', $args ) )
            add_filter( 'wpseo_breadcrumb_single_link', array( $this, 'adjust_single_breadcrumb' ) );

        if ( in_array( 'microformat_image', $args ) )
            add_filter( 'the_content', array( $this, 'jsonld_image' ), 999 );

        if ( in_array( 'remove_style_tag', $args ) )
            add_filter( 'style_loader_tag', array( $this, 'clean_style_tag' ) );

        if ( in_array( 'remove_script_tag', $args ) )
            add_filter( 'script_loader_tag', array( $this, 'clean_script_tag' ) );

    }



    public function microformat_image( $content ) {
        $pattern  = '/<img (.*?) width="(.*?)" height="(.*?)"(.*?)>/i';
        $replace = '<span itemprop="image" itemscope itemtype="https://schema.org/ImageObject"><img itemprop="url image" \\1 width="\\2" height="\\3" \\4><meta itemprop="width" content="\\2"><meta itemprop="height" content="\\3"></span>';
        $content = preg_replace( $pattern, $replace, $content );
        return $content;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function jsonld_image( $content ) {
        // Регулярное выражение ищет тег <img> с атрибутами width и height
        $pattern = '/<img (.*?) width="(.*?)" height="(.*?)"(.*?)>/i';

        $content = preg_replace_callback( $pattern, function( $matches ) {
            // Собираем оригинальный тег <img>
            $imgTag = '<img ' . $matches[1] . ' width="' . $matches[2] . '" height="' . $matches[3] . '"' . $matches[4] . '>';

            // Ищем атрибут src внутри тега
            if ( preg_match( '/src=["\']([^"\']+)["\']/', $imgTag, $srcMatches ) ) {
                $src = $srcMatches[1];
            } else {
                $src = '';
            }

            // Формируем JSON‑LD для ImageObject
            $jsonld = array(
                '@context' => 'https://schema.org',
                '@type'    => 'ImageObject',
                'url'      => $src,
                'width'    => $matches[2],
                'height'   => $matches[3],
            );
            $json = json_encode( $jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

            // Возвращаем исходный тег <img> с добавлением JSON‑LD скрипта сразу после него
            return $imgTag . '<script type="application/ld+json">' . $json . '</script>';
        }, $content );

        return $content;
    }


    /**
     * Remove last item from breadcrumbs SEO by YOAST
     * http://www.wpdiv.com/remove-post-title-yoast-seo-plugin-breadcrumb/
     */
    public function adjust_single_breadcrumb( $link_output) {
        if( strpos( $link_output, 'breadcrumb_last' ) !== false ) {
            $link_output = '';
        }
        return $link_output;
    }



    /**
     * Remove word Category, Tag in archives
     */
    public function get_the_archive_title_change( $title ) {
        if ( is_category() ) {
            $title = single_cat_title( '', false );
        } elseif ( is_tag() ) {
            $title = single_tag_title( '', false );
        }
        return $title;
    }


    /**
     * Remove hentry from post classes
     */
    public function remove_hentry_from_post_classes( $classes ) {
        $classes = str_replace( 'hentry', '', $classes );
        return $classes;
    }


    /**
     * Remove current link in menu
     *
     * @param $nav_menu
     * @param $args
     * @return mixed
     */
    public function remove_current_links_from_menu( $nav_menu, $args ) {
        preg_match_all( '/<li(.+?)class="(.+?)current-menu-item(.+?)>(<a(.+?)>(.+?)<\/a>)/ui', $nav_menu, $matches );

        if ( isset( $matches[4] ) && ! empty( $matches[4] ) && preg_match( '/<a/ui', $matches[4][0] ) ) {
            foreach ( $matches[4] as $k => $v ) {
                $classes = 'removed-link';
                if ( apply_filters( 'wpshop_core_remove_current_links:preserve_classes', false ) ) {
                    $classes = trim( $matches[2][ $k ] ) . ' ' . $classes;
                }
                if ( ! is_paged() ) {
                    $nav_menu = str_replace( $v, '<span class="' . $classes . '">' . $matches[6][ $k ] . '</span>', $nav_menu );
                }
            }
        }

        return $nav_menu;
    }


    /**
     * Remove h2 from pagination and navigation
     * Remove role="navigation" for best validation w3
     */
    public function change_navigation_markup_template( $template, $class ) {
        $template = '
	<nav class="navigation %1$s">
		<div class="screen-reader-text">%2$s</div>
		<div class="nav-links">%3$s</div>
	</nav>';
        return $template;
    }


    /**
     * Add class customize-preview to body
     */
    public function body_class_customizer() {
        add_filter( 'body_class', [ $this, 'body_class_customizer_filter' ] );
    }

    /**
     * Filter to Add class customize-preview to body
     *
     * @param $classes
     *
     * @return array
     */
    function body_class_customizer_filter( $classes ) {
        if ( is_customize_preview() ) {
            $classes[] = 'customize-preview';
        }

        return $classes;
    }


    /**
     *
     *
     * @return string
     */
    public function get_microdata_publisher() {

        $wpshop_core = new Core( $this->options );
        $logotype_image = $wpshop_core->get_option( 'logotype_image' );

        $out = '';
        $out .= '<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="display: none;">';

        if ( ! empty( $logotype_image ) ) {
            $out .= '<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">';
            $out .= '<img itemprop="url image" src="' . $logotype_image . '" alt="' . get_bloginfo('name') . '">';
            $out .= '</div>';
        }

        $out .= '<meta itemprop="name" content="' . get_bloginfo( 'name' ) . '">';
        $out .= '<meta itemprop="telephone" content="' . apply_filters( 'wpshop_microdata_publisher_telephone', get_bloginfo( 'name' ) ) . '">';
        $out .= '<meta itemprop="address" content="' . apply_filters( 'wpshop_microdata_publisher_address', get_bloginfo( 'url' ) ) . '">';
        $out .= '</div>';

        $out = apply_filters( 'wpshop_microdata_publisher_output', $out );

        return $out;
    }


    /**
     *  Remove text/css and text/javascript in styles and scripts
     */
    public function clean_style_tag( $src ) {
        return str_replace( "type='text/css'", '', $src );
    }

    public function clean_script_tag( $src ) {
        return str_replace( "type='text/javascript'", '', $src );
    }


    /**
     * Output category with Yoast support and microformat
     *
     * @param array|string $args {
     *      Optional. Array or post or post id.
     *
     *      @type int|\WP_Post   $post       Post id or post object
     *                                      Default empty
     *      @type string        $classes    Classes
     *                                      Default empty
     *      @type boolean       $micro      Microformat enable
     *                                      Default ' itemprop="articleSection"'
     *      @type string        $micro_out  Microformat text
     *                                      Default true
     *      @type boolean       $link       Link or text output
     *                                      Default true
     * }
     * @return bool|string
     */
    public function get_category( $args = array() ) {

        // Args prefixed with an underscore are reserved for internal use.
        $defaults = array(
            'post'      => '',
            'classes'   => '',
            'micro'     => true,
            'micro_out' => ' itemprop="articleSection"',
            'link'      => true,
        );
        $args = wp_parse_args($args, $defaults);

        if ( is_array( $args ) ) {
            $post = $args['post'];
        } else {
            $post = null;
        }

        if ( ! $post = get_post( $post ) )
            return false;

        $classes_out = '';
        if ( ! empty( $args['classes'] ) ) $classes_out = ' class="' . $args['classes'] . '"';

        $category = get_the_category( $post->ID );
        $cat_id = $category[0]->cat_ID;

        if ( class_exists( '\WPSEO_Primary_Term' ) ) {
            $primary_cat = new \WPSEO_Primary_Term( 'category', $post->ID );
            $primary_cat = $primary_cat->get_primary_term();
            if ( $primary_cat ) {
                $cat_id = $primary_cat;
            }
        }

        if ( $args['micro'] ) {
            $micro_out = $args['micro_out'];
        } else {
            $micro_out = '';
        }

        if ( $args['link'] ) {
            $link = get_term_link( $cat_id );

            // catch wp_error
            if ( is_wp_error( $link ) ) {
                $link = '#';
            }
            return '<a href="' . $link . '"' . $micro_out . $classes_out . '>' . get_cat_name( $cat_id ) . '</a>';
        } else {
            return '<span' . $micro_out . $classes_out . '>' . get_cat_name( $cat_id ) . '</span>';
        }

    }


    /**
     * Out views number
     *
     * @ver         1.3
     * @updated     2020-04-13      add filter wpshop_core_views_counter__meta_field
     *
     * @param int|\WP_Post $post Optional. Post ID or post object. Defaults to current post.
     *
     * @return int
     */
    public function get_views( $post = null ) {

        $post = get_post( $post );
        if ( ! $post ) {
            return '';
        }

        $meta_field = apply_filters( 'wpshop_core_views_counter__meta_field', ViewsCounter::META_FIELD );
        $post_views = intval( get_post_meta( $post->ID, $meta_field, true ) );

        return $post_views;
    }


    /**
     * Get likes count
     *
     * @ver         1.1
     * @updated     2019-04-19
     * @param int|\WP_Post $post    Optional. Post ID or post object. Defaults to current post.
     * @return int
     */
    public function get_likes( $post = null ) {

        $post = get_post( $post );
        if ( ! $post ) return '';

        $post_views = intval( get_post_meta( $post->ID, 'likes', true ) );
        return $post_views;
    }


}
