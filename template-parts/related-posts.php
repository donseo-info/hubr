<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @version 1.0
 */

/**
 * @var array{'related_posts':WP_Post[], 'post':WP_Post} $args
 */

use function WPShop\WPCommunity\get_setting;

if ( ! $args['related_posts'] ) {
    echo '<!-- no related posts found -->';

    return;
}
?>
<div class="related-posts">
    <div class="related-posts__header"><?php echo esc_html( get_setting( 'related.title' ) ) ?></div>
    <div class="related-posts__items">
        <?php foreach ( $args['related_posts'] as $post ) :
            setup_postdata( $post );
            get_template_part( 'template-parts/post-card' );
        endforeach;
        wp_reset_postdata(); ?>
    </div>
</div>

