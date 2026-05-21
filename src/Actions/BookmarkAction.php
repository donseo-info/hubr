<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Bookmark;

class BookmarkAction {

    /**
     * @var Bookmark
     */
    protected $bookmark;

    /**
     * @param Bookmark $bookmark
     */
    public function __construct( Bookmark $bookmark ) {
        $this->bookmark = $bookmark;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_bookmark_process';
            add_action( "wp_ajax_{$action}", [ $this, '_save_bookmark' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_save_bookmark' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _save_bookmark() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! $this->bookmark->can_user_bookmark() ) {
            wp_send_json_error( new WP_Error( 'cant_vote', __( 'Sign in to add to bookmarks', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['post_id'] ) ) {
            wp_send_json_error( new WP_Error( 'wrong_data', __( 'Wrong data', 'wpcommunity' ) ) );
        }

        $post = get_post( $_REQUEST['post_id'] );
        if ( ! $post ) {
            wp_send_json_error( new WP_Error( 'post_not_found', __( 'Post not found', 'wpcommunity' ) ) );
        }

        $post_bookmarks_count = $this->bookmark->save_bookmark( get_current_user_id(), $post->ID );

        // не выводим 0, чтобы не расстраивать участников
        if ( $post_bookmarks_count == 0 ) {
            $post_bookmarks_count = '';
        }

        wp_send_json_success( [ 'count' => $post_bookmarks_count ] );

    }
}
