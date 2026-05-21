<?php
/**
 * The template for displaying archive pages
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\Database\Subs;
use WPShop\WPCommunity\Features\Karma;
use WPShop\WPCommunity\Linkify;
use WPShop\WPCommunity\Helper;
use WPShop\WPCommunity\Social;
use WPShop\WPCommunity\User;
use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\get_the_attributes;
use function WPShop\WPCommunity\the_follow_button;
use function WPShop\WPCommunity\theme_container;

get_header();

$user_instance = theme_container()->get( User::class );
$helper        = theme_container()->get( Helper::class );
$karma         = theme_container()->get( Karma::class );

global $authordata;
$user_id = isset( $authordata->ID ) ? $authordata->ID : 0;

$user_login       = get_the_author_meta( 'login' );
$user_description = get_the_author_meta( 'description' );
$user_description = strip_tags( $user_description, '<b><i><s><strong><a><em>' );
$user_description = nl2br( $user_description );
$user_description = theme_container()->get( Linkify::class )->linkify( $user_description );

$user_registered = get_the_author_meta( 'registered' );
$registered_days = $helper->days_between_dates( $user_registered, date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );
$followers_count = theme_container()->get( Subs::class )->get_count( User::FOLLOW_TYPE_USER, $user_id );

/**
 * Allows you to set the user name on the author page
 *
 * [ru] Позволяет задать имя пользователя на странице автора
 *
 * @hooked \WPShop\WPCommunity\Membership::_append_membership_badge()
 *
 * @since 1.0
 */
$user_name = apply_filters( 'wpcommunity/author/name', $user_instance->get_user_name( $user_id ), $user_id );

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

                <main id="primary" class="site-main">

                    <div class="site-main-inner"><?php /* для расположения блоков flex column с отступом gap */ ?>

                        <div class="author-box">

                            <header class="author-header">
                                <div class="author-header__avatar">
                                    <?php echo get_avatar( $user_id, 150 ); ?>
                                </div>
                                <div class="author-header__body">
                                    <h1 class="author-header__name">
                                        <?php echo $user_name ?>
                                    </h1>
                                    <div class="author-header__username">@<?php echo $user_login ?></div>
                                    <div class="author-header__registered">

                                <span class="karma" data-tooltip="<?php _e( 'Karma', 'wpcommunity' ) ?>"
                                      data-tooltip-pos="bottom">
                                    <svg><use xlink:href="#ico-karma"></use></svg>
                                    <?php echo $karma->get_karma( $user_id ) ?>
                                </span>

                                        <span data-tooltip="<?php echo esc_attr( date_i18n( get_option( 'date_format' ), strtotime( $user_registered ) ) ) ?>"
                                              data-tooltip-pos="bottom">
                                <strong><?php echo $registered_days ?></strong>
                                <?php echo _n( 'day on site', 'days on site', $registered_days, 'wpcommunity' ); ?>
                                </span>

                                        <span>
                                    <strong class="js-wpcommunity-subscribers-count"
                                            data-type="<?php echo User::FOLLOW_TYPE_USER ?>"
                                            data-target="<?php echo esc_attr( $user_id ) ?>"><?php echo esc_html( $followers_count ) ?></strong>
                                    <?php echo _n( 'subscriber', 'subscribers', $followers_count, 'wpcommunity' ) ?>
                                </span>
                                    </div>
                                </div>
                                <div class="author-header__actions">
                                    <?php if ( get_current_user_id() != $user_id ) : ?>
                                        <?php the_follow_button( User::FOLLOW_TYPE_USER, $user_id ) ?>
                                    <?php endif; ?>
                                </div>
                            </header><!-- .page-header -->

                            <?php if ( ! empty( $user_description ) ): ?>
                                <div class="author-description"><?php echo $user_description ?></div>
                            <?php endif; ?>

                            <?php get_template_part( 'template-parts/elements/social-profiles', null, compact( 'user_id' ) ) ?>

                            <?php
                            //                            $achievements = get_posts( [ 'post_type' => 'achievement' ] );
                            //                            if ( ! empty( $achievements ) ) {
                            //                                echo '<div class="user-achievements">';
                            //                                foreach ( $achievements as $achievement ) {
                            //                                    for ( $i = 1; $i <= 5; $i ++ ) {
                            //
                            //                                        $description = get_post_meta( $achievement->ID, 'description', true );
                            //
                            //                                        echo '<div class="user-achievement">';
                            //                                        echo '  <div class="user-achievement__image">';
                            //                                        echo '  <img src="' . get_template_directory_uri() . '/assets/public/images/achievements/achievement-first.svg">';
                            //                                        echo '  </div>';
                            //                                        echo '  <div class="user-achievement__title">' . get_the_title( $achievement->ID ) . '</div>';
                            //                                        echo '  <div class="user-achievement__description">' . $description . '</div>';
                            //                                        echo '</div>';
                            //                                    }
                            //                                }
                            //                                echo '</div>';
                            //                            }
                            ?>
                        </div>

                        <?php if ( have_posts() ) : ?>

                            <?php

                            echo '<div class="post-cards">';

                            while ( have_posts() ) :
                                the_post();

                                get_template_part( 'template-parts/post-card' );

                            endwhile;
                            wp_reset_postdata();

                            echo '</div><!--.post-cards-->';

                            the_posts_pagination();

                        else : ?>
                            <div class="post-cards">
                                <article id="post-48"
                                         class="post-card post-card--format-post post-48 post type-post status-publish format-standard hentry category-1">
                                    <div class="post-card__content">
                                        <?php echo esc_html__( 'Nothing written yet', 'wpcommunity' ) ?>
                                    </div><!-- .entry-content -->
                                </article>
                            </div>
                        <?php endif; ?>

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
                do_action( 'wpcommunity/main/after', 'template-subs' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
