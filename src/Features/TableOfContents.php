<?php

namespace WPShop\WPCommunity\Features;

use Wpshop\Core\ThemeOptions;
use WPShop\WPCommunity\Core\TableOfContentsBuilder;
use WPShop\WPCommunity\Customizer\Customizer;

class TableOfContents {

    /**
     * @var Customizer
     */
    protected $customizer;

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
        $options = new class extends \Wpshop\Core\ThemeOptions {
            public function __construct() {
                $this->text_domain = 'wpcommunity';
                $this->theme_slug  = 'wpcommunity';
            }
        };
        $core    = new class( $options ) extends \Wpshop\Core\Core {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct( ThemeOptions $options ) {
                $this->theme_options = $options;
            }

            /**
             * @param string       $element
             * @param int|\WP_Post $post
             *
             * @return bool
             */
            public function is_show_element( $element = '', $post = 0 ) {
                $post = get_post( $post );

                if ( ! is_singular() ) {
                    return true;
                }

                if ( empty( $post->ID ) ) {
                    return false;
                }

                if ( empty( $element ) ) {
                    return false;
                }

                $hide_elements = (array) ( get_post_meta( $post->ID, 'hide_elements', true ) ?: [] );

                return $element && ! in_array( $element, $hide_elements );
            }
        };

        $toc = new TableOfContentsBuilder( $options, null, $core );
        $toc->init();

        /**
         * Allows you to control initialization of toc from core
         *
         * [ru] Позволяет влиять на инициализацию toc из core
         *
         * @since 1.1
         */
        do_action(
            'wpcommunity/toc/init_toc',
            $toc
        );

        add_filter( 'wpcommunity/toc/single', [ $this, '_show_in_posts' ] );
        add_filter( 'wpcommunity/toc/page', [ $this, '_show_in_pages' ] );
        add_filter( 'wpcommunity/toc/open', [ $this, '_set_opened' ] );
        add_filter( 'wpcommunity/toc/noindex', [ $this, '_set_wrapped_in_noindex' ] );
        add_filter( 'wpcommunity/toc/place', [ $this, '_set_toc_position' ] );
        add_filter( 'wpcommunity/toc/title', [ $this, '_set_toc_title' ] );
    }

    /**
     * @return bool
     */
    public function _show_in_posts() {
        return (bool) $this->customizer->get_option( 'toc.enable_post' );
    }

    /**
     * @return bool
     */
    public function _show_in_pages() {
        return (bool) $this->customizer->get_option( 'toc.enable_page' );
    }

    /**
     * @return bool
     */
    public function _set_opened() {
        return (bool) $this->customizer->get_option( 'toc.opened' );
    }

    /**
     * @return bool
     */
    public function _set_wrapped_in_noindex() {
        return (bool) $this->customizer->get_option( 'toc.wrap_no_index' );
    }

    /**
     * @return string
     */
    public function _set_toc_position() {
        if ( $this->customizer->get_option( 'toc.prepend_content' ) ) {
            return 'before_header';
        }

        return 'after_content';
    }

    /**
     * @param string $result
     *
     * @return string
     */
    public function _set_toc_title( $result ) {
        $title = trim( $this->customizer->get_option( 'toc.title' ) );

        return $title ?: $result;
    }
}
