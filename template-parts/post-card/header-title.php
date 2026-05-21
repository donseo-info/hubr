<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\ElementAttributes\PostCardContext;
use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\the_attributes;
use function WPShop\WPCommunity\theme_container;

$post_instance = theme_container()->get( Post::class );
$post_format   = $post_instance->get_post_format( $post->ID );
?>

<h2 <?php the_attributes( new PostCardContext( 'h2.post-card__title' ), [ 'classes' => 'post-card__title' ] ); ?>>
    <?php
    if ( $post_format != 'post' ) :
        ?>
        <span class="post-card__format" data-tooltip="<?php echo esc_attr( $post_instance->get_format_title( $post_format ) ); ?>">
            <svg width="24" height="24"><use xlink:href="#ico-<?php echo esc_attr( $post_format ) ?>"></use></svg>
        </span>
    <?php endif ?>

    <span <?php the_attributes( new PostCardContext( 'h2.post-card__title-link-wrap' ) ); ?>>
        <a href="<?php echo esc_url( get_permalink() ) ?>" rel="bookmark"><?php the_title() ?></a>
    </span>
</h2>
