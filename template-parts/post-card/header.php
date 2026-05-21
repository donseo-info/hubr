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

<header class="post-card__header">
    <div class="post-meta post-card__meta">
        <?php

        /**
         * @since 1.0
         */
        do_action( 'wpcommunity/post_card/header_meta' );
        ?>
    </div>

    <?php

    /**
     * @since 1.0
     */
    do_action( 'wpcommunity/post_card/header' );
    ?>

</header><!-- .post-card__header -->
