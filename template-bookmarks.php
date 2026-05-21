<?php
/**
 * Template Name: Bookmarks
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\Features\Feeds;
use function WPShop\WPCommunity\theme_container;

get_header();
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
                do_action( 'wpcommunity/main/before', 'template-bookmarks' );
                ?>

                <main id="primary" class="site-main">

                    <?php
                    if ( is_user_logged_in() ) {
                        theme_container()->get( Feeds::class )->query_bookmarks();

                        if ( have_posts() ) :

                            echo '<div class="post-cards">';

                            /**
                             * Main posts loop hook
                             *
                             * [ru] Основной хук цикла вывода постов
                             *
                             * @hooked \WPShop\WPCommunity\Layout\Layout::_main_loop()
                             * @hooked \WPShop\WPCommunity\Layout\Layout::_the_posts_pagination(), 15
                             *
                             * @since 1.0
                             */
                            do_action( 'wpcommunity/posts/loop' );

                            echo '</div><!--.post-cards-->';

                        else :
                            ?>
                            <div class="post-card">
                                <h1 class="page-title"><?php esc_html_e( 'No bookmarks', 'wpcommunity' ); ?></h1>

                                <p><?php echo esc_html__( 'You haven\'t added anything to your bookmarks yet.', 'wpcommunity' ) ?></p>
                                <p><?php echo esc_html__( 'You can save interesting entries to your bookmarks so that you don\'t lose them.', 'wpcommunity' ) ?></p>
                                <p><?php echo esc_html__( 'To add an entry to your bookmarks, click on the symbol', 'wpcommunity' ) ?>
                                    <svg width="20" height="20">
                                        <use xlink:href="#ico-bookmark"></use>
                                    </svg>
                                    .
                                </p>
                            </div>
                        <?php

                        endif;

                        theme_container()->get( Feeds::class )->reset_query();
                    } else {
                        ?>
                        <div class="post-card">
                            <h1 class="page-title"><?php esc_html_e( 'No bookmarks', 'wpcommunity' ); ?></h1>

                            <p><?php echo esc_html__( 'The bookmark page is available only to authorized users.', 'wpcommunity' ) ?></p>
                        </div>
                        <?php
                    }
                    ?>

                </main><!-- #main -->

                <?php

                /**
                 * After main content hook
                 *
                 * [ru] Хук после выводом основного контентом
                 *
                 * @hooked \WPShop\WPCommunity\Features\RelatedProducts::_output_related_posts()
                 *
                 * @since 1.0
                 */
                do_action( 'wpcommunity/main/after', 'template-bookmarks' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
