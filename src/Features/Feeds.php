<?php

namespace WPShop\WPCommunity\Features;

use WP_Query;
use WPShop\WPCommunity\Bookmark;
use WPShop\WPCommunity\Context;
use WPShop\WPCommunity\Data\Sub;
use WPShop\WPCommunity\Database\Subs;
use WPShop\WPCommunity\Layout\LoopContext;
use WPShop\WPCommunity\User;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

/**
 */
class Feeds {


    /**
     * @var Subs
     */
    protected $subs;

    /**
     * @var Bookmark
     */
    protected $bookmarks;

    /**
     * @var LoopContext
     */
    protected $loop_context;

    /**
     * @var array|null
     */
    protected $_replace_where_parts;

    /**
     * @param Subs        $subs
     * @param Bookmark    $bookmarks
     * @param LoopContext $loop_context
     */
    public function __construct(
        Subs $subs,
        Bookmark $bookmarks,
        LoopContext $loop_context
    ) {
        $this->subs         = $subs;
        $this->bookmarks    = $bookmarks;
        $this->loop_context = $loop_context;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp', function () {
            $this->loop_context->set_context( Context::createFromWpQuery() );
        } );

        add_action( 'wp', [ $this, '_disable_pagination' ] );

        add_filter( 'wpcommunity/assets/script_globals', [ $this, '_append_params_to_script_globals' ] );

        add_filter( 'wpcommunity/post-card/full-text', [ $this, '_show_full_content' ] );
        add_filter( 'wpcommunity/post_card/show_full_text', [ $this, '_show_full_content' ] );
    }

    /**
     * @param bool $result
     *
     * @return bool
     */
    public function _show_full_content( $result ) {
        $context = $this->loop_context->get_context();

        if ( $context->is_home ) {
            if ( get_setting( 'content.full.home' ) ) {
                $result = true;
            }
        } elseif ( 'post' == $context->get_object_type() &&
                   'page' === $context->get_object_subtype()
        ) {
            foreach ( [ 'popular', 'subs', 'bookmarks' ] as $key ) {
                if (
                    get_setting( "page.{$key}" ) &&
                    $context->get_object_id() == get_setting( "page.{$key}" ) &&
                    get_setting( "content.full.{$key}" )
                ) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }


    /**
     * Check the current page in main query context. Disable pagination using settings for a specific feed type.
     *
     * @return void
     */
    public function _disable_pagination() {
        $output_pagination = true;
        if ( is_home() ) {
            if ( get_setting( 'content.infinite_scroll.home' ) ) {
                $output_pagination = false;
            }
        } else {
            foreach ( [ 'popular', 'subs', 'bookmarks' ] as $key ) {
                if ( get_setting( "page.{$key}" ) &&
                     is_page( get_setting( "page.{$key}" ) ) &&
                     get_setting( "content.infinite_scroll.{$key}" )
                ) {
                    $output_pagination = false;
                    break;
                }
            }
        }

        add_filter( 'wpcommunity/layout/output_pagination', function () use ( $output_pagination ) {
            return $output_pagination;
        } );
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function _append_params_to_script_globals( $params ) {
        global $wp_query;
        $params['infinite_scroll_options'] = [
            'enabled'             => $this->enabled_by_setting(),
            'intersect_threshold' => 0.05,
            'max_pages'           => $wp_query->max_num_pages,
            'qv'                  => base64_encode( json_encode( $wp_query->query_vars ) ),
            'context'             => (string) Context::createFromWpQuery(),
            'is_page'             => is_page(),
            'page_id'             => is_page() ? get_queried_object_id() : null,
            'counter'             => theme_container()->get( LoopContext::class )->get_counter(),
        ];

        return $params;
    }

    /**
     * @param string $context
     * @param int    $paged
     * @param array  $query_vars
     *
     * @return array
     * @throws \Exception
     */
    public function get_infinite_scroll_post_cards( $context, $paged, array $query_vars = [] ) {
        $loop_context = theme_container()->get( LoopContext::class );
        $loop_context->init( $this->get_posts_per_page() * ( $paged - 1 ) );

        $context = Context::createFromParams( $context );
        if ( ! is_wp_error( $context ) ) {
            $loop_context->set_context( $context );
        }

        $query_vars['post_type']   = 'post';
        $query_vars['paged']       = $paged;
        $query_vars['post_status'] = 'publish';

        $result = [];

        $reset_query = true;
        if ( $context->is_home ) {
            query_posts( $query_vars );
        } elseif ( 'post' == $context->get_object_type() &&
                   'page' === $context->get_object_subtype()
        ) {
            switch ( $context->get_object_id() ) {
                case get_setting( 'page.popular' ):
                    $this->query_popular( $paged );
                    break;
                case get_setting( 'page.subs' ):
                    $this->query_subs( $paged );
                    break;
                case get_setting( 'page.bookmarks' ):
                    $this->query_bookmarks( $paged );
                    break;
                default:
                    $reset_query = false;
                    break;
            }
        }

        $loop_context = theme_container()->get( LoopContext::class );
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();

                $result[] = _ob_get_content( function () use ( $loop_context ) {
                    get_template_part( 'template-parts/post-card' );
                    $loop_context->increase_counter();
                } );

                $after = _ob_get_content( function () {
                    do_action( 'wpcommunity/posts_loop/after_card' );
                } );
                if ( $after ) {
                    $result[] = $after;
                }

            }
            wp_reset_postdata();
        }

        if ( $reset_query ) {
            wp_reset_query();
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function enabled_by_setting() {
        if ( is_home() ) {
            return get_setting( 'content.infinite_scroll.home' );
        }
        foreach ( [ 'popular', 'subs', 'bookmarks' ] as $key ) {
            if ( get_setting( "page.{$key}" ) && is_page( get_setting( "page.{$key}" ) ) ) {
                return get_setting( "content.infinite_scroll.{$key}" );
            }
        }

        return false;
    }

    /**
     * @param int|null $paged
     *
     * @return void
     */
    public function query_popular( $paged = null ) {
        if ( ! $paged ) {
            $paged = get_query_var( 'paged' );
        }

        query_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $this->get_posts_per_page(),
            'paged'          => $paged,
            'meta_key'       => Vote::META_SCORE,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ] );
    }

    /**
     * @param int $paged
     *
     * @return void
     */
    public function query_bookmarks( $paged = null ) {
        if ( ! $paged ) {
            $paged = get_query_var( 'paged' );
        }

        $post_ids = $this->bookmarks->get_bookmark_post_ids( get_current_user_id() );

        if ( empty( $post_ids ) ) {
            $post_ids = [ 0 ];
        }

        query_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $this->get_posts_per_page(),
            'paged'          => $paged,
            'post__in'       => $post_ids,
        ] );
    }

    /**
     * Modify main query on subscription page. The goal is to change query relation
     * from AND to OR.
     * WHERE part of db query should look like this
     * <pre>
     * ... AND ( wp_term_relationships.term_taxonomy_id IN (29) OR wp_term_relationships.term_taxonomy_id IN (30,31)  OR
     * wp_posts.post_author IN (3) ) AND ...
     * <pre>
     *
     * @param int|null $paged
     *
     * @return void
     */
    public function query_subs( $paged = null ) {
        if ( ! $paged ) {
            $paged = get_query_var( 'paged' );
        }

        $args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $this->get_posts_per_page(),
            'paged'          => $paged,
        ];

        $cat_subs   = $this->subs->get_rows_by_type( get_current_user_id(), User::FOLLOW_TYPE_CATEGORY );
        $categories = array_map( function ( Sub $sub ) {
            return $sub->get_target_obj() ? $sub->get_target_obj()->term_id : false;
        }, iterator_to_array( $cat_subs ) );

        if ( $categories ) {
            $args['category__in'] = $categories;
        }

        $tag_subs = $this->subs->get_rows_by_type( get_current_user_id(), User::FOLLOW_TYPE_TAG );
        $tags     = array_map( function ( Sub $sub ) {
            return $sub->get_target_obj() ? $sub->get_target_obj()->term_id : false;
        }, iterator_to_array( $tag_subs ) );

        if ( $tags ) {
            $args['tag__in'] = $tags;
        }

        $user_subs = $this->subs->get_rows_by_type( get_current_user_id(), User::FOLLOW_TYPE_USER );
        $authors   = array_map( function ( Sub $sub ) {
            return $sub->get_target_obj()->ID;
        }, iterator_to_array( $user_subs ) );

        if ( $authors ) {
            $args['author__in'] = $authors;

            $author__in = implode( ',', array_map( 'absint', array_unique( (array) $authors ) ) );

            if ( $categories || $tags ) {
                global $wpdb;

                $this->_replace_where_parts = [
                    ") AND {$wpdb->posts}.post_author IN ($author__in) ",
                    " OR {$wpdb->posts}.post_author IN ($author__in) ) ",
                ];

                add_filter( 'posts_where', [ $this, '_replace_where_condition' ], 10, 2 );
            }
        }

        add_action( 'pre_get_posts', function () {
            add_action( 'parse_tax_query', [ $this, '_change_tax_query_relation' ] );
        } );

        query_posts( $args );
    }

    /**
     * @return void
     */
    public function reset_query() {
        wp_reset_query();
    }

    /**
     * @param string   $where
     * @param WP_Query $query
     *
     * @return string
     */
    public function _replace_where_condition( $where, $query ) {
        remove_filter( 'posts_where', [ $this, '_replace_where_condition' ], 10, 2 );

        $where = preg_replace( '/\s+/', ' ', $where );

        $where = str_replace(
            $this->_replace_where_parts[0],
            $this->_replace_where_parts[1],
            $where
        );


        return $where;
    }

    /**
     * @param WP_Query $query
     *
     * @return void
     */
    public function _change_tax_query_relation( WP_Query $query ) {
        remove_action( 'parse_tax_query', [ $this, '_change_tax_query_relation' ] );
        if ( $query->tax_query ) {
            $query->tax_query->queries['relation'] = 'OR';
        }
    }

    /**
     * @return int
     */
    protected function get_posts_per_page() {
        return absint( get_option( 'posts_per_page' ) );
    }
}
