<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\ElementAttributes\PostCardContext;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\TemplateFunctions;
use function WPShop\WPCommunity\the_attributes;
use function WPShop\WPCommunity\theme_container;

$membership = theme_container()->get( Membership::class );

?>
<div <?php the_attributes( new PostCardContext( 'div.post-card__content' ), [ 'classes' => 'post-card__content' ] ); ?>>
    <?php

    /**
     * Allows to set show full content of card
     *
     * [ru] Позволяет установить показ полного контента в карточке
     *
     * @hooked \WPShop\WPCommunity\Customizer\Customizer\DefaultHooks::_set_excerpt_length()
     *
     * @since 1.1
     */
    $show_full_text = apply_filters( 'wpcommunity/post_card/show_full_text', false );

    if ( $show_full_text ) {

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

    /**
     * @since 1.3.0
     */
    do_action( 'wpcommunity/post_card/after_content' );

    ?>
</div>
