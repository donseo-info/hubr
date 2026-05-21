<?php

use WPShop\WPCommunity\Helper;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Post;
use WPShop\WPCommunity\User;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

$membership    = theme_container()->get( Membership::class );
$helper        = theme_container()->get( Helper::class );
$user_instance = theme_container()->get( User::class );
$author_name   = apply_filters( 'wpcommunity/post-card/author_name', $user_instance->get_user_name( $post->post_author ), $post->post_author );

$post_instance = theme_container()->get( Post::class );
$post_format   = $post_instance->get_post_format( $post->ID );
?>

<div class="post-meta post-card__meta">
    <div class="post-meta__author">
        <a href="<?php echo esc_url( get_author_posts_url( $post->post_author ) ) ?>" rel="author">
            <div class="post-meta__avatar">
                <?php echo get_avatar( $post->post_author, 24 ) ?>
            </div>
            <?php echo $author_name ?>
        </a>
    </div>
    <div class="post-meta__date">
        <?php echo $helper->beauty_date( $post->post_date ) ?>
    </div>
    <div class="post-meta__category">
        <?php wpcommunity_the_category() ?>
    </div>

    <?php theme_container()->get( Vote::class )->the_vote( $post ); ?>
</div>
