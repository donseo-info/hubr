<?php

namespace WPShop\WPCommunity\Layout;


use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Customizer\CustomizerHelper;

class PostCard {

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
        add_action( 'wpcommunity/post_card/entry', [ $this, '_output_header' ] );
        add_action( 'wpcommunity/post_card/entry', [ $this, '_output_image' ], 20 );
        add_action( 'wpcommunity/post_card/entry', [ $this, '_output_content' ], 30 );
        add_action( 'wpcommunity/post_card/entry', [ $this, '_output_footer' ], 40 );

        add_action( 'wpcommunity/post_card/header', [ $this, '_output_title' ] );

        add_action( 'wpcommunity/post_card/header_meta', [ $this, '_output_header_meta' ] );
        add_action( 'wpcommunity/post_card/header_meta', [ $this, '_output_meta' ], 20 );
        add_action( 'wpcommunity/post_card/header_meta_right', [ $this, '_output_meta' ], 20 );

        add_action( 'wpcommunity/post_card/footer_meta', [ $this, '_output_footer_meta' ] );
        add_action( 'wpcommunity/post_card/footer_meta', [ $this, '_output_meta' ], 20 );
        add_action( 'wpcommunity/post_card/footer_meta_right', [ $this, '_output_meta' ], 20 );
    }

    /**
     * @return void
     */
    public function _output_header() {
        get_template_part( 'template-parts/post-card/header' );
    }

    /**
     * @return void
     */
    public function _output_title() {
//        if ( ! CustomizerHelper::is_item_enabled( 'post_card.content_elements.title' ) ) {
//            return;
//        }
        get_template_part( 'template-parts/post-card/header-title' );
    }

    /**
     * @return void
     */
    public function _output_image() {
        if ( ! CustomizerHelper::is_item_enabled( 'post_card.content_elements.image' ) ) {
            return;
        }
        get_template_part( 'template-parts/post-card/image' );
    }

    /**
     * @return void
     */
    public function _output_content() {
        if ( ! CustomizerHelper::is_item_enabled( 'post_card.content_elements.excerpt' ) ) {
            return;
        }
        get_template_part( 'template-parts/post-card/content' );
    }

    /**
     * @return void
     */
    public function _output_footer() {
        get_template_part( 'template-parts/post-card/footer' );
    }

    /**
     * @return void
     */
    public function _output_header_meta() {
        get_template_part( 'template-parts/post-card/meta', 'header-right' );
    }

    /**
     * @return void
     */
    public function _output_footer_meta() {
        get_template_part( 'template-parts/post-card/meta', 'footer-right' );
    }

    /**
     * @return void
     */
    public function _output_meta() {
        $list = null;

        if ( doing_action( 'wpcommunity/post_card/header_meta' ) &&
             ! doing_action( 'wpcommunity/post_card/header_meta_right' )
        ) {
            $list = 'post_card.header_elements';
        }

        if ( doing_action( 'wpcommunity/post_card/header_meta_right' ) ) {
            $list = 'post_card.header_right_elements';
        }

        if ( doing_action( 'wpcommunity/post_card/footer_meta' ) &&
             ! doing_action( 'wpcommunity/post_card/footer_meta_right' )
        ) {

            $list = 'post_card.footer_elements';
        }

        if ( doing_action( 'wpcommunity/post_card/footer_meta_right' ) ) {
            $list = 'post_card.footer_right_elements';
        }

        if ( ! $list ) {
            return;
        }

        if ( CustomizerHelper::is_in_list( 'post_card.header_elements.author', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'author' );
        }
        if ( CustomizerHelper::is_in_list( 'post_card.header_elements.date', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'date' );
        }
        if ( CustomizerHelper::is_in_list( 'post_card.header_elements.category', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'category' );
        }

        if ( CustomizerHelper::is_in_list( 'post_card.header_right_elements.vote', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'vote' );
        }

        if ( CustomizerHelper::is_in_list( 'post_card.footer_elements.comments', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'comments' );
        }
        if ( CustomizerHelper::is_in_list( 'post_card.footer_elements.views', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'views' );
        }
        if ( CustomizerHelper::is_in_list( 'post_card.footer_elements.bookmarks', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'bookmarks' );
        }
        if ( CustomizerHelper::is_in_list( 'post_card.footer_elements.tags', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'tags' );
        }

        if ( CustomizerHelper::is_in_list( 'post_card.footer_right_elements.access', $list ) ) {
            get_template_part( 'template-parts/post-card/meta', 'access' );
        }
    }
}
