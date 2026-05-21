<?php

defined( 'WPINC' ) || die;

/**
 * The template for displaying search results pages
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package WPCommunity
 */

//

get_header();

$have_posts = have_posts();
?>
    <div class="site-content">
        <div class="content-area">

            <div class="content-area-inner"><?php /* для расположения блоков flex column с отступом gap */ ?>

                <?php

                /**
                 * Before main content hook
                 *
                 * [ru] Хук перед выводом основного контентом
                 *
                 * @hooked \WPShop\WPCommunity\Features\Breadcrumbs::_output_breadcrumbs()
                 * @hooked \WPShop\WPCommunity\DefaultHooks::_output_homepage_h1()
                 * @hooked \WPShop\WPCommunity\DefaultPages::_output_page_header(), 15
                 *
                 * @since 1.0
                 */
                do_action( 'wpcommunity/main/before', 'search' );
                ?>

                <main id="primary" class="site-main">

                    <div class="site-main-inner"><?php /* для расположения блоков flex column с отступом gap */ ?>

                        <div class="post-card">

                            <div class="post-card__header">
                                <h1 class="post-card__title"><?php
                                    if ( $have_posts ) {
                                        /* translators: %s: search query. */
                                        printf( esc_html__( 'Search Results for: %s', 'wpcommunity' ), '<span>' . get_search_query() . '</span>' );
                                    } else {
                                        echo esc_html__( 'Nothing found', 'wpcommunity' );
                                    }
                                    ?>
                                </h1>
                            </div>
                            <?php
                            /**
                             * Allows you to change the logic of the search form display
                             *
                             * [ru] Позволяет изменить логику отображения формы поиска
                             *
                             * @hooked \WPShop\WPCommunity\DefaultHooks::_show_search_form()
                             *
                             * @since 1.1
                             */
                            $show_search_form = apply_filters( 'wpcommunity/search/show_form', true );
                            if ( $show_search_form ): ?>
                                <div class="post-card__content">
                                    <?php get_search_form(); ?>
                                </div>
                            <?php endif ?>
                        </div>

                        <?php if ( $have_posts ) : ?>
                            <div class="post-cards">
                                <?php
                                /* Start the Loop */
                                while ( have_posts() ) :
                                    the_post();

                                    /**
                                     * Run the loop for the search to output the results.
                                     * If you want to overload this in a child theme then include a file
                                     * called content-search.php and that will be used instead.
                                     */
                                    get_template_part( 'template-parts/post-card' );

                                endwhile;
                                wp_reset_postdata();

                                the_posts_pagination();
                                ?>
                            </div><!--.post-cards-->
                        <?php else: ?>
                            <div class="post-cards">
                                <div class="post-card">
                                    <div class="post-card__content">
                                        <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'wpcommunity' ); ?></p>
                                    </div>
                                </div>
                            </div><!--.post-cards-->
                        <?php endif ?>

                    </div><!--.site-main-inner-->
                </main><!-- #main -->

            </div><!--.content-area-inner-->
        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
