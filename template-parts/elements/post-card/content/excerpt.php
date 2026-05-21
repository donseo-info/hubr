<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\TemplateFunctions;
use function WPShop\WPCommunity\theme_container;

$membership = theme_container()->get( Membership::class );

if ( apply_filters( 'wpcommunity/post-card/full-text', false ) ) {

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

} else {
    $excerpt = theme_container()->get( TemplateFunctions::class )->get_the_excerpt( get_the_excerpt() );
    if ( has_excerpt() ) {
        echo $excerpt;
    } else {
        if ( $membership->is_user_post_access() ) {
            echo $excerpt;
        } else {
            get_template_part( 'template-parts/no-access' );
        }
    }
}
