<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Layout\Profile\UserComments;
use function WPShop\WPCommunity\theme_container;

$user_comments = theme_container()->get( UserComments::class );

?>
<ul>
    <?php foreach ( $user_comments->get_comments( get_current_user_id(), $_REQUEST['comment_page'] ?? 1 ) as $comment ): ?>
        <li>
            <div>
                <?php echo get_comment_text( $comment ) ?>
            </div>
            <div class="post-meta">
                <a class="post-meta__link" href="<?php echo get_permalink( $comment->comment_post_ID ) ?>#comment-<?php echo $comment->comment_ID ?>">
                    <?php echo esc_html__( 'link', 'wpcommunity' ) ?>
                </a>
                <div class="post-meta__date">
                    <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $comment->comment_date ) ) ?>
                </div>
            </div>
        </li>
    <?php endforeach ?>
</ul>
