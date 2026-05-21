<?php
/**
 * Template Name: Popular
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
                 * [ru] Хук перед выводом основного контента
                 *
                 * @hooked \WPShop\WPCommunity\Features\Breadcrumbs::_output_breadcrumbs()
                 * @hooked \WPShop\WPCommunity\DefaultHooks::_output_homepage_h1()
                 * @hooked \WPShop\WPCommunity\DefaultPages::_output_page_header(), 15
                 *
                 * @since 1.0
                 */
                do_action( 'wpcommunity/main/before', 'template-popular' );
                ?>

                <main id="primary" class="site-main">

                    <?php
                    theme_container()->get( Feeds::class )->query_popular();

                    if ( have_posts() ) :

                        if ( is_home() && ! is_front_page() ) :
                            ?>
                            <header>
                                <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
                            </header>
                        <?php
                        endif;

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

                        get_template_part( 'template-parts/content', 'none' );

                    endif;

                    theme_container()->get( Feeds::class )->reset_query();
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
                do_action( 'wpcommunity/main/after', 'template-popular' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
