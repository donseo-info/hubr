<?php
/**
 * Template Name: Publish
 *
 * @package WPCommunity
 */

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Features\FrontendEditor;
use WPShop\WPCommunity\Membership;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

$editor = theme_container()->get( FrontendEditor::class )->register();

$show_publish_form = false;
if ( is_user_logged_in() ) {
    if ( get_setting( 'account.not_pro.create_posts' ) ||
         ! theme_container()->get( Membership::class )->is_expired()
    ) {
        $show_publish_form = true;
        $editor->grant_upload_cap();
    }
}

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
                do_action( 'wpcommunity/main/before', 'template-publish' );
                ?>

                <main id="primary" class="site-main">

                    <div class="post-card">

                        <h1><?php empty( $_GET['id'] ) ? _e( 'Add new', 'wpcommunity' ) : _e( 'Edit post', 'wpcommunity' ) ?></h1>

                        <?php
                        if ( ! is_user_logged_in() ) {
                            get_template_part( 'template-parts/no-logged-in' );
                        } else {
                            if ( $show_publish_form ) {
                                get_template_part( 'template-parts/publish/publish-form', '', compact( 'editor' ) );
                            } else {
                                get_template_part( 'template-parts/publish/no-allowed' );
                            }
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
                do_action( 'wpcommunity/main/after', 'template-publish' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
