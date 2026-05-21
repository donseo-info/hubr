<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\Membership;
use function WPShop\WPCommunity\the_attributes;
use function WPShop\WPCommunity\theme_container;

$membership = theme_container()->get( Membership::class );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>

    <header class="post-card__header">
        <h1 class="post-card__title"><?php the_title() ?></h1>
    </header><!-- .entry-header -->

    <div class="post-card__content">

        <?php wpcommunity_post_thumbnail(); ?>

        <div <?php the_attributes( 'entry-content', [ 'classes' => 'entry-content' ] ); ?>>
            <?php
            if ( has_excerpt() && ! $membership->is_user_post_access() ) {
                echo '<div class="post-card__content">';
                echo get_the_excerpt();
                echo '</div>';
            }

            the_content();

            wp_link_pages(
                [
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wpcommunity' ),
                    'after'  => '</div>',
                ]
            );
            ?>
        </div><!-- .entry-content -->

    </div>
</article><!-- #post-<?php the_ID(); ?> -->
