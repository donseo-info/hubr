<?php

namespace WPShop\WPCommunity\Layout;

use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Customizer\CustomizerHelper;

class SinglePost {

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
    public function init_actions() {
        add_action( 'wpcommunity/post/content', [ $this, '_output_header' ] );
        add_action( 'wpcommunity/post/content', [ $this, '_output_excerpt' ], 20 );
        add_action( 'wpcommunity/post/content', [ $this, '_output_image' ], 30 );
        add_action( 'wpcommunity/post/content', [ $this, '_output_content' ], 40 );
        add_action( 'wpcommunity/post/content', [ $this, '_output_footer' ], 50 );

        add_action( 'wpcommunity/post/header', [ $this, '_output_title' ] );

        add_action( 'wpcommunity/post/header_meta', [ $this, '_output_header_meta' ] );
        add_action( 'wpcommunity/post/header_meta', [ $this, '_output_meta' ] );
        add_action( 'wpcommunity/post/header_meta_right', [ $this, '_output_meta' ] );

        add_action( 'wpcommunity/post/footer_meta', [ $this, '_output_footer_meta' ] );
        add_action( 'wpcommunity/post/footer_meta', [ $this, '_output_meta' ], 20 );
        add_action( 'wpcommunity/post/footer_meta_right', [ $this, '_output_meta' ], 20 );
    }

    /**
     * @param string $post_type
     *
     * @return void
     */
    public function _output_header( $post_type = '' ) {
        get_template_part( 'template-parts/post/header', $post_type );
    }

    /**
     * @return void
     */
    public function _output_title() {
        get_template_part( 'template-parts/post/header-title' );
    }

    /**
     * @param string $post_type
     *
     * @return void
     */
    public function _output_excerpt( $post_type = '' ) {
        if ( ! CustomizerHelper::is_item_enabled( 'post.content_elements.excerpt' ) ) {
            return;
        }
        // get_template_part( 'template-parts/post/excerpt', $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return void
     */
    public function _output_image( $post_type = '' ) {
        if ( ! CustomizerHelper::is_item_enabled( 'post.content_elements.image' ) ) {
            return;
        }
        get_template_part( 'template-parts/post/image', $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return void
     */
    public function _output_content( $post_type = '' ) {
        get_template_part( 'template-parts/post/content', $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return void
     */
    public function _output_footer( $post_type = '' ) {
        get_template_part( 'template-parts/post/footer', $post_type );
    }

    /**
     * @return void
     */
    public function _output_header_meta() {
        get_template_part( 'template-parts/post/meta', 'header-right' );
    }

    /**
     * @return void
     */
    public function _output_footer_meta() {
        get_template_part( 'template-parts/post/meta', 'footer-right' );
    }

    /**
     * @return void
     */
    public function _output_meta() {
        $list = null;

        if ( doing_action( 'wpcommunity/post/header_meta' ) &&
             ! doing_action( 'wpcommunity/post/header_meta_right' )
        ) {
            $list = 'post.header_elements';
        }

        if ( doing_action( 'wpcommunity/post/header_meta_right' ) ) {
            $list = 'post.header_right_elements';
        }

        if ( doing_action( 'wpcommunity/post/footer_meta' ) &&
             ! doing_action( 'wpcommunity/post/footer_meta_right' )
        ) {

            $list = 'post.footer_elements';
        }

        if ( doing_action( 'wpcommunity/post/footer_meta_right' ) ) {
            $list = 'post.footer_right_elements';
        }

        if ( ! $list ) {
            return;
        }

        if ( CustomizerHelper::is_in_list( 'post.header_elements.author', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'author' );
        }
        if ( CustomizerHelper::is_in_list( 'post.header_elements.date', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'date' );
        }
        if ( CustomizerHelper::is_in_list( 'post.header_elements.category', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'category' );
        }

        if ( CustomizerHelper::is_in_list( 'post.header_right_elements.vote', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'vote' );
        }

        if ( CustomizerHelper::is_in_list( 'post.footer_elements.comments', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'comments' );
        }
        if ( CustomizerHelper::is_in_list( 'post.footer_elements.views', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'views' );
        }
        if ( CustomizerHelper::is_in_list( 'post.footer_elements.bookmarks', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'bookmarks' );
        }
        if ( CustomizerHelper::is_in_list( 'post.footer_elements.tags', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'tags' );
        }

        if ( CustomizerHelper::is_in_list( 'post.footer_right_elements.access', $list ) ) {
            get_template_part( 'template-parts/post/meta', 'access' );
        }
    }
}
