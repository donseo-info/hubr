<?php
/**
 * Template Name: Top
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\Features\Karma;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\User;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

get_header();

$membership    = theme_container()->get( Membership::class );
$karma         = theme_container()->get( Karma::class );
$vote          = theme_container()->get( Vote::class );
$user_instance = theme_container()->get( User::class );
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
                do_action( 'wpcommunity/main/before', 'template-top' );
                ?>

                <main id="primary" class="site-main">

                    <div class="post-card">

                        <h1><?php _e( 'Top', 'wpcommunity' ) ?></h1>

                        <h2><?php echo esc_html__( 'Longest subscription', 'wpcommunity' ) ?></h2>

                        <?php
                        $users = get_users( [
                            'meta_query' => [
                                'expired' => [
                                    'key'     => User::USER_META_EXPIRED,
                                    'value'   => current_time( 'timestamp' ),
                                    'compare' => '>',
                                    'type'    => 'UNSIGNED',
                                ]
                            ],
                            'orderby'    => [
                                'expired' => 'DESC',
                            ],
                            'number'     => 10,
                        ] );

                        if ( ! empty( $users ) ) {
                            echo '<div class="top-table">';

                            $n = 0;
                            foreach ( $users as $user ) {
                                $n ++;

                                $expired = get_user_meta( $user->ID, 'expired', true );

                                echo '<div class="top-table__item">';
                                echo '<div class="top-table__place">' . $n . '</div>';
                                echo '<div class="top-table__object">';
                                echo '  <a href="' . esc_url( get_author_posts_url( $user->ID ) ) . '" target="_blank">';
                                echo '  <div class="top-table__avatar">' . get_avatar( $user->ID, 24 ) . '</div>';
                                echo '  <div class="top-table__name">' . $user_instance->get_user_name( $user->ID ) . '</div>';
                                echo '  </a>';
                                echo '</div>';
                                echo '<div class="top-table__value">' . $membership->get_expired_days( $user->ID ) . '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                        }
                        ?>


                        <h2><?php echo esc_html__( 'Biggest karma', 'wpcommunity' ) ?></h2>

                        <?php
                        $users = get_users( [
                            'meta_query' => [
                                'karma' => [
                                    'key'     => Karma::USER_META_KARMA,
                                    'value'   => 0,
                                    'compare' => '>=',
                                    'type'    => 'NUMERIC',
                                ],
                            ],
                            'orderby'    => [
                                'karma' => 'DESC',
                            ],
                            'number'     => 10,
                        ] );

                        if ( ! empty( $users ) ) {
                            echo '<div class="top-table">';

                            $n = 0;
                            foreach ( $users as $user ) {
                                $n ++;

                                $expired = get_user_meta( $user->ID, 'expired', true );

                                echo '<div class="top-table__item">';
                                echo '<div class="top-table__place">' . $n . '</div>';
                                echo '<div class="top-table__object">';
                                echo '  <a href="' . esc_url( get_author_posts_url( $user->ID ) ) . '" target="_blank">';
                                echo '  <div class="top-table__avatar">' . get_avatar( $user->ID, 24 ) . '</div>';
                                echo '  <div class="top-table__name">' . $user_instance->get_user_name( $user->ID ) . '</div>';
                                echo '  </a>';
                                echo '</div>';
                                echo '<div class="top-table__value">';

                                echo '<span class="karma"><svg><use xlink:href="#ico-karma"></use></svg> ' . $karma->get_karma( $user->ID ) . '</span>';

                                echo '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                        }
                        ?>


                        <h2><?php echo esc_html__( 'Best posts in 3 months', 'wpcommunity' ) ?></h2>

                        <?php
                        $posts = get_posts( [
                            'date_query'     => [
                                'after' => '90 days ago',
                            ],
                            'meta_query'     => [
                                'relation'   => 'AND',
                                'vote_score' => [
                                    'key'     => Vote::META_SCORE,
                                    'value'   => '0',
                                    'compare' => '>=',
                                    'type'    => 'NUMERIC',
                                ],
                            ],
                            'orderby'        => [
                                'vote_score' => 'DESC',
                            ],
                            'posts_per_page' => 10,
                        ] );

                        if ( ! empty( $posts ) ) {
                            echo '<div class="top-table">';

                            $n = 0;

                            foreach ( $posts as $post ) {
                                $n ++;

                                echo '<div class="top-table__item">';
                                echo '<div class="top-table__place">' . $n . '</div>';
                                echo '<div class="top-table__object">';
                                echo '  <a href="' . esc_url( get_the_permalink( $post->ID ) ) . '" target="_blank">';
                                echo '  <div class="top-table__name">' . get_the_title( $post->ID ) . '</div>';
                                echo '  </a>';
                                echo '</div>';
                                echo '<div class="top-table__value">';

                                echo $vote->get_vote_score( 'post', $post->ID );

                                echo '</div>';
                                echo '</div>';

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
                do_action( 'wpcommunity/main/after', 'template-top' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
