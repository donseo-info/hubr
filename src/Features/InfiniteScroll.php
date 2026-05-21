<?php

namespace WPShop\WPCommunity\Features;

use WPShop\WPCommunity\Context;
use WPShop\WPCommunity\Layout\LoopContext;
use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

/**
 * @deprecated
 */
class InfiniteScroll {

    /**
     * @return void
     */
    public function init() {
//        add_action( 'wp', [ $this, '_prepare' ] );
//        add_filter( 'wpcommunity/assets/script_globals', [ $this, '_append_params_to_script_globals' ] );
    }

    /**
     * @return void
     */
    public function _prepare() {
        if ( ! $this->enabled_by_setting() ) {
            return;
        }

        remove_action( 'wpcommunity/posts/loop', 'the_posts_pagination', 15 );
    }

    /**
     * @return bool
     */
    protected function enabled_by_setting() {
        if ( is_home() ) {
            return get_setting( 'content.infinite_scroll.home' );
        }
//        var_dump(is_page('page.bookmarks'));die;
        foreach ( [ 'popular', 'subs', 'bookmarks' ] as $key ) {
            if ( get_setting( "page.{$key}" ) && is_page( get_setting( "page.{$key}" ) ) ) {
                return get_setting( "content.infinite_scroll.{$key}" );
            }
        }

        return false;
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
     * @param string $query_vars
     * @param string $context
     * @param int    $page
     *
     * @return array
     * @throws \Exception
     */
    public function get_post_cards( $query_vars, $context, $page ) {
        if ( $query_vars ) {
            $query_vars = base64_decode( $query_vars );
            $query_vars = json_decode( $query_vars, true );

            $query_vars['paged']       = $page;
            $query_vars['post_status'] = 'publish';
        } else {
            return [];
        }


        query_posts( $query_vars );

        $loop_context = theme_container()->get( LoopContext::class );
        $loop_context->init( ( $query_vars['posts_per_page'] ?? 0 ) * ( $page - 1 ) );

        $context = Context::createFromParams( $context );
        if ( ! is_wp_error( $context ) ) {
            $loop_context->set_context( $context );
        }

        $result = [];
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

//        $result[] = '<article class="post-card post-card--format-post"><p>text</p><script id="script_id">alert("test")</script></article>';

        return $result;
    }
}
