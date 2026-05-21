<?php

/**
 * Template Name: Subscriptions
 *
 * @package WPCommunity
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Features\Feeds;
use WPShop\WPCommunity\User;
use WPShop\WPCommunity\Features\UserSubscriptions;
use function WPShop\WPCommunity\the_follow_button;
use function WPShop\WPCommunity\theme_container;

$user_subscriptions = theme_container()->get( UserSubscriptions::class );

$user_subs_count     = 0;
$category_subs_count = 0;
$tag_subs_count      = 0;


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
                do_action( 'wpcommunity/main/before', 'template-subs' );
                ?>

                <main id="primary" class="site-main js-wpcommunity-sub-tabs">
                    <?php if ( is_user_logged_in() ): ?>

                        <div class="post-card follow-list">

                            <div class="follow-list-tabs follow-list__tabs">
                                <div class="follow-list-tabs__item js-wpcommunity-sub-tab active" data-target="posts">
                                    <?php echo esc_html__( 'Posts', 'wpcommunity' ) ?>
                                </div>
                                <div class="follow-list-tabs__item js-wpcommunity-sub-tab"
                                     data-target="users-and-topics">
                                    <?php echo esc_html__( 'My Subscriptions', 'wpcommunity' ) ?>
                                </div>
                            </div>

                            <div class="follow-list__tab-content js-wpcommunity-sub-tab-content"
                                 data-tab="users-and-topics" style="display: none">

                                <h2><?php echo esc_html__( 'Users', 'wpcommunity' ) ?></h2>
                                <?php foreach ( $user_subscriptions->get_subs( User::FOLLOW_TYPE_USER, null, true ) as $sub ): $user_subs_count ++; ?>
                                    <?php if ( $target_obj = $sub->get_target_obj() ): ?>
                                        <div class="follow-list-item js-user-subscription">
                                            <a href="<?php echo get_author_posts_url( $target_obj->ID ) ?>"><?php echo esc_html( theme_container()->get( User::class )->get_user_name( $target_obj ) ) ?></a>
                                            <?php the_follow_button( User::FOLLOW_TYPE_USER, $sub->target, true ) ?>
                                        </div>
                                    <?php endif ?>
                                <?php endforeach ?>

                                <h2><?php echo esc_html__( 'Topics', 'wpcommunity' ) ?></h2>
                                <?php foreach ( $user_subscriptions->get_subs( User::FOLLOW_TYPE_CATEGORY, null, true ) as $sub ): $category_subs_count ++; ?>
                                    <?php if ( $target_obj = $sub->get_target_obj() ): ?>
                                        <div class="follow-list-item js-user-subscription">
                                            <a href="<?php echo get_term_link( $target_obj->term_id ) ?>"><?php echo esc_html( $target_obj->name ) ?></a>
                                            <?php the_follow_button( User::FOLLOW_TYPE_CATEGORY, $sub->target, true ) ?>
                                        </div>
                                    <?php endif ?>
                                <?php endforeach ?>

                                <h2><?php echo esc_html__( 'Tags', 'wpcommunity' ) ?></h2>
                                <?php
                                foreach ( $user_subscriptions->get_subs( User::FOLLOW_TYPE_TAG, null, true ) as $sub ): $tag_subs_count ++; ?>
                                    <?php if ( $target_obj = $sub->get_target_obj() ): ?>
                                        <div class="follow-list-item js-user-subscription">
                                            <a href="<?php echo get_term_link( $target_obj->term_id ) ?>"><?php echo esc_html( $target_obj->name ) ?></a>
                                            <?php the_follow_button( User::FOLLOW_TYPE_TAG, $sub->target, true ) ?>
                                        </div>
                                    <?php endif ?>
                                <?php endforeach ?>

                            </div>
                        </div>

                        <div class="js-wpcommunity-sub-tab-content post-cards" data-tab="posts">
                            <?php

                            if ( $user_subs_count || $category_subs_count || $tag_subs_count )  :

                                theme_container()->get( Feeds::class )->query_subs();

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

                                theme_container()->get( Feeds::class )->reset_query();
                            endif;

                            ?>
                        </div>

                    <?php else: ?>
                        <div class="post-card">
                            <?php echo get_template_part( 'template-parts/no-logged-in' ) ?>
                        </div>
                    <?php endif ?>

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
                do_action( 'wpcommunity/main/after', 'template-subs' );
                ?>

            </div><!--.content-area-inner-->
        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
