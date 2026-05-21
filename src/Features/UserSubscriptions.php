<?php

namespace WPShop\WPCommunity\Features;

use WP_Query;
use WPShop\WPCommunity\Data\Sub;
use WPShop\WPCommunity\Database\Subs;
use WPShop\WPCommunity\User;
use function WPShop\WPCommunity\get_setting;

class UserSubscriptions {

    /**
     * @var Subs
     */
    protected $subs;

    /**
     * @var array|null
     */
    protected $_replace_where_parts;

    /**
     * @param Subs $subs
     */
    public function __construct( Subs $subs ) {
        $this->subs = $subs;
    }

    /**
     * @return void
     * @deprecated
     */
    public function init() {
//        add_action( 'pre_get_posts', [ $this, '_prepare_query' ] );
    }

    /**
     * @param WP_Query $query
     *
     * @return void
     * @see wp-includes/class-wp-query.php:2361
     */
    public function _prepare_query( $query ) {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $page_id = get_setting( 'page.subs' );
        if ( ! $page_id ) {
            return;
        }

        $page = get_post( $page_id );
        if ( ! $page_id ) {
            return;
        }

        /**
         * @since 1.0
         */
        $prepare = apply_filters(
            'wpcommunity/user_subscription/prepare_query',
            $page->post_name === $query->get( 'pagename' ) && ! is_admin() && $query->is_main_query()
        );

        if ( $prepare ) {

            add_action( 'parse_tax_query', [ $this, '_change_tax_query_relation' ] );

            $query->set( 'post_type', 'post' );
            $query->set( 'pagename', null );
            $query->set( 'posts_per_page', get_option( 'posts_per_page' ) );

            $cat_subs   = $this->subs->get_rows_by_type( get_current_user_id(), User::FOLLOW_TYPE_CATEGORY );
            $categories = array_map( function ( Sub $sub ) {
                return $sub->get_target_obj() ? $sub->get_target_obj()->term_id : false;
            }, iterator_to_array( $cat_subs ) );

            $query->set( 'category__in', $categories );

            $tag_subs = $this->subs->get_rows_by_type( get_current_user_id(), User::FOLLOW_TYPE_TAG );
            $tags     = array_map( function ( Sub $sub ) {
                return $sub->get_target_obj() ? $sub->get_target_obj()->term_id : false;
            }, iterator_to_array( $tag_subs ) );

            $query->set( 'tag__in', $tags );

            $user_subs = $this->subs->get_rows_by_type( get_current_user_id(), User::FOLLOW_TYPE_USER );
            $authors   = array_map( function ( Sub $sub ) {
                return $sub->get_target_obj()->ID;
            }, iterator_to_array( $user_subs ) );

            if ( $authors ) {
                $query->set( 'author__in', $authors );

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

            // Support for paging
            $query->is_singular = 0;

            // set custom template
            add_filter( 'template_include', [ $this, '_set_template' ], 101 );
        }
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
     * @param string   $where
     * @param WP_Query $query
     *
     * @return string
     */
    public function _replace_where_condition( $where, $query ) {
        remove_filter( 'posts_where', [ $this, '_replace_where_condition' ], 10, 2 );

        $where = preg_replace( '/\s+/', ' ', $where );

//        var_dump( $this->_replace_where_parts );
//        var_dump( $where );
        $where = str_replace(
            $this->_replace_where_parts[0],
            $this->_replace_where_parts[1],
            $where
        );

//        var_dump( $where );
//        die;

        return $where;
    }

    /**
     * @param string $template
     *
     * @return string
     */
    public function _set_template( $template ) {
        $target_tpl = 'template-subs.php';

        remove_filter( 'template_include', [ $this, '_set_template' ], 101 );

        $new_template = locate_template( [ $target_tpl ] );

        if ( ! empty( $new_template ) ) {
            $template = $new_template;
        };

        return $template;
    }

    /**
     * @param      $type User::FOLLOW_TYPE_USER, User::FOLLOW_TYPE_CATEGORY
     * @param      $user_id
     * @param bool $merge_target_objects
     *
     * @return \Generator
     */
    public function get_subs( $type, $user_id = null, $merge_target_objects = false ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return $this->subs->get_rows_by_type( $user_id, $type, $merge_target_objects );
    }
}
