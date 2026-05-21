<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\theme_container;


$post_instance = theme_container()->get( Post::class );
$post_format   = $post_instance->get_post_format( $post->ID );
?>
<h2 class="post-card__title">
    <?php
    if ( $post_format != 'post' ) {
        echo '<span class="post-card__format" data-tooltip="' . $post_instance->get_format_title( $post_format ) . '">';
        echo '  <svg width="24" height="24"><use xlink:href="#ico-' . $post_format . '"></use></svg>';
        echo '</span>';
    }
    ?>

    <a href="<?php echo esc_url( get_permalink() ) ?>" rel="bookmark"><?php the_title() ?></a>
</h2>
