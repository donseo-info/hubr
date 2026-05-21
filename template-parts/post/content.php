<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\ElementAttributes\PostContext;
use WPShop\WPCommunity\Membership;
use function WPShop\WPCommunity\the_attributes;
use function WPShop\WPCommunity\theme_container;


$membership = theme_container()->get( Membership::class );
?>

<div <?php the_attributes( new PostContext( 'div.entry-content' ), [ 'classes' => 'entry-content' ] ); ?>>
    <?php
    if ( $membership->is_user_post_access() ) {

        do_action( 'wpcommunity/post_content/before' );

        the_content(
            sprintf(
                wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'wpcommunity' ),
                    [
                        'span' => [
                            'class' => [],
                        ],
                    ]
                ),
                wp_kses_post( get_the_title() )
            )
        );

        do_action( 'wpcommunity/post_content/after' );

        wp_link_pages(
            [
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wpcommunity' ),
                'after'  => '</div>',
            ]
        );
    } else {
        get_template_part( 'template-parts/no-access' );
    }
    ?>
</div><!-- .entry-content -->
