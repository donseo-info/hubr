<?php
/**
 * The template for displaying all single posts
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\Data\ElementAttributes\PostContext;
use function WPShop\WPCommunity\the_attributes;

wp_enqueue_script( 'wpcommunity-goodshare' );

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
                do_action( 'wpcommunity/main/before', 'single' );

                ?>
                <main <?php the_attributes( new PostContext( 'main.site-main' ), [
                    'id'      => 'primary',
                    'classes' => 'site-main',
                ] ); ?>>

                    <div class="site-main-inner"><?php /* для расположения блоков flex column с отступом gap */ ?>

                        <?php
                        while ( have_posts() ) :
                            the_post();

                            get_template_part( 'template-parts/content', get_post_type() );

                            /**
                             * Hook for comments output
                             *
                             * [ru] Хук вывода комментариев
                             *
                             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_social_share()
                             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_comments(), 20
                             * @hooked \WPShop\WPCommunity\Features\Advertisement::_insert_post_ad_before_comments(), 15
                             * @hooked \WPShop\WPCommunity\Features\Advertisement::_insert_post_ad_after_comments(), 25
                             *
                             * @since 1.0
                             */
                            do_action( 'wpcommunity/comments/output', 'single' );

                        endwhile; // End of the loop.
                        wp_reset_postdata();
                        ?>

                    </div><!--.site-main-inner-->

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
                do_action( 'wpcommunity/main/after', 'single' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
