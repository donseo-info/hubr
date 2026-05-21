<?php

use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Features\ViewsCounter;
use function WPShop\WPCommunity\theme_container;

$membership = theme_container()->get( Membership::class );
$views      = theme_container()->get( ViewsCounter::class );
$customizer = theme_container()->get( Customizer::class );

?>
<footer class="post-card__footer">
    <div class="post-meta">
        <div class="post-meta__comments">
            <a href="<?php echo esc_url( get_permalink() ) ?>#comments">
                <svg width="20" height="20">
                    <use xlink:href="#ico-comment"></use>
                </svg>
                <?php echo get_comments_number() ?>
            </a>
        </div>

        <?php if ( $customizer->get_option( 'structure.post.show_views_count' ) ): ?>
            <div class="post-meta__views">
                <svg width="20" height="20">
                    <use xlink:href="#ico-eye"></use>
                </svg>
                <?php $views->the_views(); ?>
            </div>
        <?php endif ?>

        <div class="post-meta__bookmark">
            <?php get_template_part( 'template-parts/components/bookmark' ) ?>
        </div>

        <div class="post-meta__tags">
            <?php
            $post_tags = get_the_tags();
            if ( $post_tags ) {
                foreach ( $post_tags as $tag ) {
                    echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . $tag->name . '</a> ';
                }
            }
            ?>
        </div>

        <div class="post-meta__right">
            <div class="post-meta__access">
                <?php if ( $membership->is_post_access() ) : ?>
                    <span class="post-meta__access-public" data-tooltip="<?php _e( 'Public', 'wpcommunity' ) ?>" data-tooltip-pos="bottom">
                    <svg width="20" height="20"><use xlink:href="#ico-public"></use></svg>
                </span>
                <?php else : ?>

                    <?php if ( $membership->is_user_post_access() ) : ?>
                        <span class="post-meta__access-unlock" data-tooltip="<?php _e( 'Private', 'wpcommunity' ) ?>" data-tooltip-pos="bottom">
                        <svg width="16" height="20"><use xlink:href="#ico-unlock"></use></svg>
                    </span>
                    <?php else : ?>
                        <span class="post-meta__access-lock" data-tooltip="<?php _e( 'Private', 'wpcommunity' ) ?>" data-tooltip-pos="bottom">
                        <svg width="16" height="20"><use xlink:href="#ico-lock"></use></svg>
                    </span>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</footer><!-- .entry-footer -->
