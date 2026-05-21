<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WPCommunity
 */

?>

<section class="post-card no-results not-found">
    <header class="post-card__header">
        <h1 class="post-card__title"><?php esc_html_e( 'Nothing Found', 'wpcommunity' ); ?></h1>
    </header>

    <div class="post-card__content">
        <?php
        if ( is_home() && current_user_can( 'publish_posts' ) ) :

            printf(
                '<p>' . wp_kses(
                /* translators: 1: link to WP admin new post page. */
                    __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'wpcommunity' ),
                    [
                        'a' => [
                            'href' => [],
                        ],
                    ]
                ) . '</p>',
                esc_url( admin_url( 'post-new.php' ) )
            );

        elseif ( is_search() ) :
            ?>

            <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'wpcommunity' ); ?></p>
            <?php
            get_search_form();

        else :
            ?>

            <p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'wpcommunity' ); ?></p>

            <?php get_search_form(); ?>

        <?php endif ?>
    </div><!-- .page-content -->
</section><!-- .no-results -->
