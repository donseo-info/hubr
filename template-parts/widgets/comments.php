<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Comments;
use function WPShop\WPCommunity\substring_by_word;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'comments':WP_Comment[]} $args
 */

$comments = theme_container()->get( Comments::class );


?>

<div class="widget widget-comments">
    <?php if ( ! empty( $args['header'] ) ): ?>
        <div class="widget-title widget-comments__header">
            <?php echo esc_html( $args['header'] ) ?>
        </div>
    <?php endif ?>
    <div class="widget-comments__body widget-comments-list">
        <?php foreach ( $args['comments'] as $comment ): ?>
            <div class="widget-comments-item">
                <div class="widget-comments-item__author">
                    <?php echo $comments->get_author_link_with_avatar( $comment, 24 ); ?>
                </div>
                <div class="widget-comments-item__content">
                    <a href="<?php echo get_comment_link( $comment ) ?>">
                        <?php echo substring_by_word( wp_strip_all_tags( get_comment_text( $comment->ID ) ), 130, ' ', '...' ) ?>
                    </a>
                </div>
                <div class="widget-comments-item__post-link">
                    <a href="<?php echo get_permalink( $comment->comment_post_ID ) ?>"><?php echo get_the_title( $comment->comment_post_ID ) ?></a>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
