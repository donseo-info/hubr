<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$context = 'footer';

use WPShop\WPCommunity\User;
use function WPShop\WPCommunity\theme_container;

$user_instance = theme_container()->get( User::class );
$author_name   = apply_filters( 'wpcommunity/post-card/author_name', $user_instance->get_user_name( $post->post_author ), $post->post_author );
?>

<div class="post-meta__author post-card__author">
    <a href="<?php echo esc_url( get_author_posts_url( $post->post_author ) ) ?>" rel="author">
        <div class="post-meta__avatar">
            <?php echo get_avatar( $post->post_author, 24 ) ?>
        </div>
        <?php echo $author_name ?>
    </a>
</div>
