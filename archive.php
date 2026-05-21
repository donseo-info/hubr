<?php
/**
 * The template for displaying archive pages
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\User;
use function WPShop\WPCommunity\the_follow_button;

get_header();

$sub_target = $sub_type = null;
if ( is_category() ) {
    $category = get_category( get_query_var( 'cat' ) );
    if ( $category && ! is_wp_error( $category ) ) {
        $sub_type   = User::FOLLOW_TYPE_CATEGORY;
        $sub_target = $category->term_id;
    }
}
if ( is_tag() ) {
    $sub_type   = User::FOLLOW_TYPE_TAG;
    $sub_target = get_queried_object_id();
}

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
                do_action( 'wpcommunity/main/before', 'archive' );
                ?>

                <main id="primary" class="site-main">

                    <div class="site-main-inner"><?php /* для расположения блоков flex column с отступом gap */ ?>

                        <?php if ( have_posts() ) : ?>


                            <header class="page-header">

                                <h1 class="page-title"><?php the_archive_title() ?></h1>
                                <?php if ( $sub_target ): ?>
                                    <?php the_follow_button( $sub_type, $sub_target ); ?>
                                <?php endif ?>
                                <div class="archive-description"><?php the_archive_description() ?>
                                </div>
                            </header><!-- .page-header -->

                            <?php

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

                            //the_posts_pagination();

                        else :

                            get_template_part( 'template-parts/content', 'none' );

                        endif;
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
                do_action( 'wpcommunity/main/after', 'archive' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
