<?php
/**
 * Template Name: Profile
 * Text Domain:   wpcommunity
 *
 * @package WPCommunity
 */

__( 'Profile', 'wpcommunity' );

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
                do_action( 'wpcommunity/main/before', 'template-account' );
                ?>

                <main id="primary" class="site-main">

                    <div class="post-card">

                        <?php

                        if ( ! is_user_logged_in() ) {
                            get_template_part( 'template-parts/profile/auth-form' );
                        } else {
                            get_template_part( 'template-parts/profile/profile' );
                        }
                        ?>

                    </div>

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
                do_action( 'wpcommunity/main/after', 'template-account' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
