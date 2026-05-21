<?php

/**
 * @version 1.0
 */

use function WPShop\WPCommunity\the_attributes;

?>

<article <?php the_attributes( 'article.post-card', [
    'id'      => 'post-' . get_the_ID(),
    'classes' => get_post_class( 'post-card' ),
] ); ?>>

    <?php
    /**
     * @since 1.0
     *
     * @hooked \WPShop\WPCommunity\Layout\SinglePost::_output_header()
     * @hooked \WPShop\WPCommunity\Layout\SinglePost::_output_excerpt(), 20
     * @hooked \WPShop\WPCommunity\Layout\SinglePost::_output_image(), 30
     * @hooked \WPShop\WPCommunity\Layout\SinglePost::_output_content(), 40
     * @hooked \WPShop\WPCommunity\Layout\SinglePost::_output_footer(), 50
     * @hooked \WPShop\WPCommunity\Features\MicroData::_add_article_meta(), 100
     */
    do_action( 'wpcommunity/post/content', get_post_type() );
    ?>

</article><!-- #post-<?php the_ID(); ?> -->
