<?php

namespace WPShop\WPCommunity\Widgets;

use Walker_Comment;
use WPShop\WPCommunity\Comments;
use function WPShop\WPCommunity\theme_container;

class CommentWalker extends Walker_Comment {

    /**
     * @inheridoc
     *
     * @param \WP_Comment $comment
     * @param int         $depth
     * @param array       $args
     *
     * @return void
     */
    protected function html5_comment( $comment, $depth, $args ) {

        $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

        $comments = theme_container()->get( Comments::class );

        $avatar         = $comments->get_comment_avatar( $comment, $args['avatar_size'] );
        $by_post_author = $this->is_comment_by_post_author( $comment );

        ?>
        <<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static output ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>

        <?php if ( $depth > 1 ) {
            echo '<div class="comment-branch js-comment-branch"></div>';
        } ?>

        <div id="div-comment-<?php comment_ID(); ?>"
             class="comment-body js-comment"
             data-comment-id="<?php comment_ID() ?>">

            <div class="comment-header">
                <span class="comment-header__avatar js-comment-avatar"><?php echo wp_kses_post( $avatar ); ?></span>
                <div class="comment-header__author">
                    <span class="comment-header__author-name js-comment-author-name"><?php echo $comments->get_author_link( $comment ); ?></span>
                    <?php if ( $by_post_author ): ?>
                        <span class="comment-header__by-author"><?php echo __( 'author', 'wpcommunity' ) ?></span>
                    <?php endif ?>
                </div>
            </div><!-- .comment-header -->

            <?php
            if ( '0' === $comment->comment_approved ) {
                ?>
                <div class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'wpcommunity' ); ?></div>
                <?php
            }
            ?>

            <div class="comment-content js-comment-content">
                <?php comment_text(); ?>
            </div><!-- .comment-content -->
        </div><!-- .comment-body -->

        <?php
    }

    public function is_comment_by_post_author( $comment = null ) {
        if ( is_object( $comment ) && $comment->user_id > 0 ) {
            $user = get_userdata( $comment->user_id );
            $post = get_post( $comment->comment_post_ID );

            if ( ! empty( $user ) && ! empty( $post ) ) {

                return $comment->user_id === $post->post_author;

            }
        }

        return false;
    }
}
