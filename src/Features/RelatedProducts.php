<?php

namespace WPShop\WPCommunity\Features;

use WP_Post;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class RelatedProducts {

    /**
     * @return void
     */
    public function init() {
        // отключаем вывод плагина yet-another-related-posts-plugin на хуке the_content
        add_filter( 'noyarpp', '__return_true' );

        add_action( 'wpcommunity/main/after', [ $this, '_output_related_posts' ] );
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function _output_related_posts( $context ) {
        if ( 'single' !== $context ) {
            return;
        }

        if ( ! get_setting( 'related.enabled' ) ) {
            return;
        }

        /**
         * @since 1.0
         */
        $related_yarpp_enabled = apply_filters( 'wpcommunity/related/yarpp_enabled', false );

        if ( $related_yarpp_enabled && function_exists( 'yarpp_related' ) ) {
            yarpp_related();

            return;
        }

        global $post;
        if ( $post ) {
            $related_posts = theme_container()->get( RelatedProducts::class )->get_related( $post );
            get_template_part( 'template-parts/related-posts', '', compact( 'related_posts', 'post' ) );
        }
    }

    /**
     * @return array
     */
    public static function search_by_options() {
        return [
            'tags'               => __( 'Tags', 'wpcommunity' ),
            'categories'         => __( 'Categories', 'wpcommunity' ),
            'tag_and_categories' => __( 'Tags & Categories', 'wpcommunity' ),
        ];
    }

    /**
     * @return array
     */
    public static function order_options() {
        return [
            'date_desc' => __( 'Date (new earlier)', 'wpcommunity' ),
            'date_asc'  => __( 'Date (old earlier)', 'wpcommunity' ),
            'rand'      => __( 'Random', 'wpcommunity' ),
            'karma'     => __( 'Karma', 'wpcommunity' ),
        ];
    }

    /**
     * @param WP_Post $post
     *
     * @return array
     */
    public function get_related( $post ) {

        /**
         * @since 1.0
         */
        $related_count = apply_filters(
            'wpcommunity/related/count',
            min( 1000, max( 1, absint( get_setting( 'related.count' ) ) ) ),
            $post
        );

        /**
         * @since 1.0
         */
        $related_posts = apply_filters( 'wpcommunity/related/posts', [] );

        // сначала смотрим, что задано в мета поле у поста
        if ( count( $related_posts ) < $related_count ) {
            $delta = $related_count - count( $related_posts );

            $posts_not_in[] = $post->ID;
            foreach ( $related_posts as $_post ) {
                $posts_not_in[] = $_post->ID;
            }

            $additional_related = $this->get_related_from_post( $post, $delta, $posts_not_in );

            $related_posts = array_merge( $related_posts, $additional_related );
        }

        // дальше ищем по меткам и тегам
        if ( count( $related_posts ) < $related_count ) {
            $delta = $related_count - count( $related_posts );

            foreach ( $related_posts as $_post ) {
                $posts_not_in[] = $_post->ID;
            }
            $posts_not_in = array_unique( $posts_not_in );

            $additional_related = $this->get_related_from_taxonumies( $post, $delta, $posts_not_in );

            $related_posts = array_merge( $related_posts, $additional_related );
        }

        // в конце добираем рандомно
        if ( count( $related_posts ) < $related_count ) {
            $delta = $related_count - count( $related_posts );

            foreach ( $related_posts as $article ) {
                $posts_not_in[] = $article->ID;
            }
            $posts_not_in = array_unique( $posts_not_in );

            $additional_related = $this->get_related_random( $post, $delta, $posts_not_in );

            $related_posts = array_merge( $related_posts, $additional_related );
        }


        return $related_posts;
    }

    /**
     * @param WP_Post $post
     * @param int     $count
     * @param int[]   $exclude
     *
     * @return WP_Post[]
     */
    protected function get_related_from_post( $post, $count, $exclude = [] ) {
        $related_post_ids = wp_parse_id_list( (string) get_post_meta( $post->ID, 'related_post_ids', true ) );

        if ( empty( $related_post_ids ) ) {
            $related_post_ids = [ 0 ];
        }

        $args = $this->with_order( [
            'posts_per_page' => $count,
            'post__in'       => $related_post_ids,
            'post__not_in'   => $exclude,
        ] );

        /**
         * @since 1.0
         */
        $args = apply_filters( 'wpcommunity/related/from_post_args', $args, $post, $count, $exclude );

        return get_posts( $args );
    }

    /**
     * @param WP_Post $post
     * @param int     $count
     * @param int[]   $excluide
     *
     * @return WP_Post[]
     */
    protected function get_related_from_taxonumies( $post, $count, $excluide = [] ) {
        $args = [
            'posts_per_page' => $count,
            'post__not_in'   => $excluide,
        ];

        $use_categories = $use_tags = false;
        switch ( get_setting( 'related.search_by' ) ) {
            case 'categories':
                $use_categories = true;
                break;
            case 'tags':
                $use_tags = true;
                break;
            case 'tag_and_categories':
                $use_categories = true;
                $use_tags       = true;
            default:
                break;
        }

        // todo добавить стратегию проверки меток и категорий, на которые подписан пользователь

        if ( $use_categories ) {
            $excluded_categories = wp_parse_id_list( get_setting( 'related.exclude_categories' ) );

            $category_ids = [];
            if ( $categories = get_the_category( $post->ID ) ) {
                foreach ( $categories as $_category ) {
                    if ( ! in_array( $_category->term_id, $excluded_categories ) ) {
                        $category_ids[] = $_category->term_id;
                    }
                }
            }

            if ( $category_ids ) {
                $args['category__in'] = $category_ids;
            }
        }

        if ( $use_tags ) {
            $excluded_tags = wp_parse_id_list( get_setting( 'related.exclude_tags' ) );

            $tag_ids = [];
            if ( ( $tags = get_the_tags( $post->ID ) ) && ! is_wp_error( $tags ) ) {
                foreach ( $tags as $tag ) {
                    if ( ! in_array( $tag->term_id, $excluded_tags ) ) {
                        $tag_ids[] = $tag->term_taxonomy_id;
                    }
                }
            }

            if ( $tag_ids ) {
                $args['tag__in'] = $tag_ids;
            }
        }

        $args = $this->with_order( $args );

        /**
         * @since 1.0
         */
        $args = apply_filters( 'wpcommunity/related/from_taxonomies_args', $args, $post, $count, $excluide );

        return get_posts( $args );
    }

    /**
     * @param WP_Post $post
     * @param int     $count
     * @param int[]   $exclude
     *
     * @return int[]|WP_Post[]
     */
    public function get_related_random( $post, $count, $exclude ) {
        $excluded_categories = wp_parse_id_list( get_setting( 'related.exclude_categories' ) );
        $excluded_categories = array_map( function ( $id ) {
            return '-' . absint( $id );
        }, $excluded_categories );

        $args = $this->with_order( [
            'posts_per_page' => $count,
            'post__not_in'   => $exclude,
            'orderby'        => 'rand',
            'category'       => $excluded_categories,
        ] );

        /**
         * @since 1.0
         */
        $args = apply_filters( 'wpcommunity/related/random', $args, $post, $count, $exclude );

        return get_posts( $args );
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function with_order( $args ) {
        switch ( get_setting( 'related.order' ) ) {
            case 'date_desc':
                $args['order']   = 'DESC';
                $args['orderby'] = 'date';
                break;
            case 'date_asc':
                $args['order']   = 'ASC';
                $args['orderby'] = 'date';
                break;
            case 'rand':
                $args['orderby'] = 'rand';
                break;
            case 'karma':
                $args['meta_key'] = 'vote_score';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            default:
                break;
        }

        return $args;
    }
}
