<?php

/**
 * YARPP Template: WPCommunity YARPP Template
 * Author: WPShop
 * Description: WPCommunity YARPP Template
 *
 * @version 1.0
 */

use function WPShop\WPCommunity\get_setting;

?>

<?php if ( have_posts() ): ?>
    <div class="related-posts">
        <div class="related-posts__header"><?php echo esc_html( get_setting( 'related.title' ) ) ?></div>
        <div class="related-posts__items">
            <?php while ( have_posts() ) : the_post(); ?>
                <!-- (<?php the_score(); ?>)-->
                <?php get_template_part( 'template-parts/post-card' ) ?>
            <?php endwhile ?>
        </div>
    </div>
<?php else: ?>
    <!-- no related posts found -->
<?php endif ?>
