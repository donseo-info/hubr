<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Comments;
use function WPShop\WPCommunity\_ob_get_content;

class CommentAction {

    /**
     * @var Comments
     */
    protected $comments;

    /**
     * @param Comments $comments
     */
    public function __construct( Comments $comments ) {
        $this->comments = $comments;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_comments_show';
            add_action( "wp_ajax_{$action}", [ $this, '_show_comments' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_show_comments' ] );

            $action = 'wpcommunity_comment_delete';
            add_action( "wp_ajax_{$action}", [ $this, '_delete_comment' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_delete_comment' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _show_comments() {
        global $post;
        global $withcomments;

        $post         = get_post( $_REQUEST['post'] );
        $withcomments = true;

        wp_send_json_success( [
            'html' => _ob_get_content( 'comments_template', '/template-parts/comments.php' ),
        ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _delete_comment() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['comment_id'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_data', __( 'Empty data', 'wpcommunity' ) ) );
        }

        $comment_id = $_REQUEST['comment_id'];
        $comment    = get_comment( $comment_id );

        // todo возможность удалять свои комменты

        if ( empty( $comment ) ) {
            wp_send_json_error( new WP_Error( 'comment_not_found', __( 'Comment not found', 'wpcommunity' ) ) );
        }

        // todo может ли удалять

        // восстанавливаем или удаляем коммент
        if ( $this->comments->is_trash( $comment->comment_ID ) ) {

            /**
             * Hook before deleting a comment
             *
             * [ru] Хук перед удалением комментария
             *
             * @hooked \WPShop\WPCommunity\Features\Karma::hook_comment_post()
             *
             * @since 1.0
             */
            do_action( 'wpcommunity/comments/soft_delete_undo', $comment->comment_ID, $comment );

            delete_comment_meta( $comment->comment_ID, Comments::COMMENT_META_TRASH );
        } else {

            /**
             * Hook before putting the comment in the trash
             *
             * [ru] Хук перед помещение комментария в корзину
             *
             * @hooked \WPShop\WPCommunity\Features\Karma::hook_delete_comment()
             *
             * @since 1.0
             */
            do_action( 'wpcommunity/comments/soft_delete', $comment->comment_ID, $comment );

            update_comment_meta( $comment->comment_ID, Comments::COMMENT_META_TRASH, 1 );
        }

        $comment_text = get_comment_text( $comment->comment_ID );
        $comment_text = apply_filters( 'comment_text', $comment_text, $comment, [] );

        wp_send_json_success( [
            'comment_text' => $comment_text,
            'author_name'  => $this->comments->get_author_link( $comment ),
            'avatar'       => $this->comments->get_comment_avatar( $comment, 64 ),
        ] );
    }
}
