<?php

use WPShop\WPCommunity\Comments;
use WPShop\WPCommunity\Helper;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

/**
 * @deprecated
 * @todo move to src under namespace
 */
class WPCommunity_Walker_Comment extends Walker_Comment {

	/**
	 * Outputs a comment in the HTML5 format.
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int $depth Depth of the current comment.
	 * @param array $args An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {

		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

		$comments = theme_container()->get( Comments::class );

		$avatar         = $comments->get_comment_avatar( $comment, $args['avatar_size'] );
		$by_post_author = $this->is_comment_by_post_author( $comment );

		$is_trash = $comments->is_trash( $comment->comment_ID );

		?>
        <<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static output ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>

		<?php if ( $depth > 1 ) {
			echo '<div class="comment-branch js-comment-branch"></div>';
		} ?>

        <div id="div-comment-<?php comment_ID(); ?>" class="comment-body js-comment"
             data-comment-id="<?php comment_ID() ?>">

            <div class="comment-header">
                <span class="comment-header__avatar js-comment-avatar"><?php echo wp_kses_post( $avatar ); ?></span>

                <div class="comment-header__author">
                    <span class="comment-header__author-name js-comment-author-name"><?php echo $comments->get_author_link( $comment ); ?></span>

					<?php
					if ( $by_post_author ) {
						echo '<span class="comment-header__by-author">' . __( 'author', 'wpcommunity' ) . '</span>';
					}

					if ( get_edit_comment_link() ) {
						echo ' <span aria-hidden="true">&nbsp;</span> ';
						if ( ! $is_trash ) {
							echo '<span class="pseudo-link js-comment-delete" data-toggle-text="' . __( 'Restore', 'wpcommunity' ) . '">' . __( 'Delete', 'wpcommunity' ) . '</span>';
						} else {
							echo '<span class="pseudo-link js-comment-delete" data-toggle-text="' . __( 'Delete', 'wpcommunity' ) . '">' . __( 'Restore', 'wpcommunity' ) . '</span>';
						}
//                        printf(
//                            ' <span aria-hidden="true">&bull;</span> <a class="comment-edit-link" href="%s">%s</a>',
//                            esc_url( get_edit_comment_link() ),
//                            __( 'Edit', 'twentytwenty' )
//                        );
					}
					?>
                    <div class="comment-header__date">
						<?php echo theme_container()->get( Helper::class )->beauty_date( get_comment_date( 'Y-m-d H:i:s' ) ); ?>
                    </div>
                </div>

	            <?php theme_container()->get( Vote::class )->the_vote( $comment ); ?>

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


			<?php

			$comment_reply_link = get_comment_reply_link(
				array_merge(
					$args,
					array(
						'add_below' => 'div-comment',
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'before'    => '<span class="comment-reply">',
						'after'     => '</span>',
					)
				)
			);

			if ( $comment_reply_link ) {
				?>

                <footer class="comment-footer">

					<?php
					if ( $comment_reply_link ) {
						echo $comment_reply_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Link is escaped in https://developer.wordpress.org/reference/functions/get_comment_reply_link/
					}
					?>

                </footer>

				<?php
			}
			?>

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
