<?php

namespace Wpshop\Core;

/**
 * Class ViewsCounter
 * @package Wpshop\Core
 *
 * Changelog
 * 
 * 2022-03-02   fix script_handle from theme_slug to theme_name
 * 2021-05-26   add public method increment_views, add wpshop_core_views_counter:count_views
 * 2021-05-17   add support wp fastest cache, add wpshop_core_views_counter:cache_enabled
 */
class ViewsCounter {

    /**
     * @since 1.2
     */
    const META_FIELD = 'views';

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var array|null
     */
    protected $_views_options;

    /**
     * @var ThemeOptions
     */
    protected $options;

    /**
     * @var AdminNotices
     */
    protected $notices;

    /**
     * @var string
     */
    protected $action_prefix;

    /**
     * @var string
     */
    protected $script_handle;

    /**
     * ViewsCounter constructor.
     *
     * @param Core         $core
     * @param ThemeOptions $options
     * @param AdminNotices $notices
     * @param string|null  $script_handle
     */
    public function __construct( Core $core, ThemeOptions $options, AdminNotices $notices, $script_handle = null ) {
        $this->core          = $core;
        $this->options       = $options;
        $this->notices       = $notices;
        $this->action_prefix = $options->theme_slug;
        $this->script_handle = $script_handle ?: $options->theme_name;
    }

    /**
     * @return void
     */
    public function init() {
        $views_options = $this->views_options();

        if ( ! $views_options['enabled'] ) {
            return;
        }

        $this->notices->add_notice( 'postviews_exists', function ( $close_link ) {
            if ( $this->is_postviews_enabled() ) {
                echo '<div class="notice notice-info">';
                printf( '<a href="%s" class="notice-dismiss" style="position: static; float: right; text-decoration: none;"></a>', $close_link );
                echo '<p>' .
                     __( 'Your site does appear to be using WP-PostViews plugin. You can disable it because the theme already can track post views.', $this->options->text_domain ) .
                     '</p>';
                echo '</div>';
            }
        } );

        if ( ! $this->enabled() ) {
            return;
        }

        add_action( 'wp_head', [ $this, '_process_views' ] );

        add_action( 'wp_ajax_wpshop_views_counter', [ $this, 'increment_views_ajax' ] );
        add_action( 'wp_ajax_nopriv_wpshop_views_counter', [ $this, 'increment_views_ajax' ] );

        add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_scripts' ] );

        add_filter( 'query', [ $this, '_filter_postviews_uninstall' ] );

        add_action( 'wpshop_core_views_counter:count_views', [ $this, 'increment_views' ] );

        do_action( __METHOD__, $this );
    }

    /**
     * @return array
     */
    protected function views_options() {
        if ( null === $this->_views_options ) {
            $this->_views_options = wp_parse_args( [
                'enabled'      => $this->core->get_option( 'wpshop_views_counter_enable' ),
                'count'        => $this->core->get_option( 'wpshop_views_counter_to_count' ),
                'exclude_bots' => $this->core->get_option( 'wpshop_views_counter_exclude_bots' ),
            ], $this->_views_options );
        }

        return $this->_views_options;
    }

    /**
     * @param int|\WP_Post|null $post
     */
    public function increment_views( $post = null ) {
        $post = get_post( $post );

        if ( $post ) {
            $meta_field = apply_filters( 'wpshop_core_views_counter__meta_field', self::META_FIELD );
            $count      = get_post_custom( $post->ID );
            if ( array_key_exists( $meta_field, $count ) && isset( $count[ $meta_field ][0] ) ) {
                $count = (int) $count[ $meta_field ][0];
            } else {
                $count = 0;
            }
            $count ++;
            update_post_meta( $post->ID, $meta_field, $count );
            do_action( $this->action_prefix . '_increment_views', $count );
        }
    }

    /**
     * @return void
     */
    public function increment_views_ajax() {
        if ( empty( $_GET['id'] ) ) {
            return;
        }

        if ( ! $this->cache_enabled() ) {
            return;
        }

        $meta_field = apply_filters( 'wpshop_core_views_counter__meta_field', self::META_FIELD );

        $id = (int) sanitize_key( $_GET['id'] );
        if ( $id > 0 ) {
            $count = get_post_custom( $id );
            if ( array_key_exists( $meta_field, $count ) && isset( $count[ $meta_field ][0] ) ) {
                $count = (int) $count[ $meta_field ][0];
            } else {
                $count = 0;
            }
            $count ++;
            update_post_meta( $id, $meta_field, $count );
            do_action( $this->action_prefix . '_increment_views_ajax', $count );
            wp_send_json_success( [
                [
                    'id'    => $id,
                    'count' => $count,
                ],
            ] );
        }
        wp_send_json_error();
    }

    /**
     * @return void
     */
    public function _process_views() {
        global $user_ID, $post;
        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }

        if ( ! $post ) {
            return;
        }

        $meta_field = apply_filters( 'wpshop_core_views_counter__meta_field', self::META_FIELD );

        if ( ! wp_is_post_revision( $post ) && ! is_preview() ) {
            if ( is_single() || is_page() ) {
                $id            = (int) $post->ID;
                $views_options = $this->_views_options;
                if ( ! $post_views = get_post_meta( $post->ID, $meta_field, true ) ) {
                    $post_views = 0;
                }
                $should_count = false;
                switch ( (int) $views_options['count'] ) {
                    case 0:
                        $should_count = true;
                        break;
                    case 1:
                        if ( empty( $_COOKIE[ USER_COOKIE ] ) && (int) $user_ID === 0 ) {
                            $should_count = true;
                        }
                        break;
                    case 2:
                        if ( (int) $user_ID > 0 ) {
                            $should_count = true;
                        }
                        break;
                }
                if ( isset( $views_options['exclude_bots'] ) && (int) $views_options['exclude_bots'] === 1 ) {
                    $bots      = [
                        'Google Bot'    => 'google',
                        'MSN'           => 'msnbot',
                        'Alex'          => 'ia_archiver',
                        'Lycos'         => 'lycos',
                        'Ask Jeeves'    => 'jeeves',
                        'Altavista'     => 'scooter',
                        'AllTheWeb'     => 'fast-webcrawler',
                        'Inktomi'       => 'slurp@inktomi',
                        'Turnitin.com'  => 'turnitinbot',
                        'Technorati'    => 'technorati',
                        'Yahoo'         => 'yahoo',
                        'Findexa'       => 'findexa',
                        'NextLinks'     => 'findlinks',
                        'Gais'          => 'gaisbo',
                        'WiseNut'       => 'zyborg',
                        'WhoisSource'   => 'surveybot',
                        'Bloglines'     => 'bloglines',
                        'BlogSearch'    => 'blogsearch',
                        'PubSub'        => 'pubsub',
                        'Syndic8'       => 'syndic8',
                        'RadioUserland' => 'userland',
                        'Gigabot'       => 'gigabot',
                        'Become.com'    => 'become.com',
                        'Baidu'         => 'baiduspider',
                        'so.com'        => '360spider',
                        'Sogou'         => 'spider',
                        'soso.com'      => 'sosospider',
                        'Yandex'        => 'yandex',
                    ];
                    $useragent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
                    foreach ( $bots as $name => $lookfor ) {
                        if ( ! empty( $useragent ) && ( false !== stripos( $useragent, $lookfor ) ) ) {
                            $should_count = false;
                            break;
                        }
                    }
                }
                $should_count = apply_filters( 'wpshop_should_count', $should_count, $id );
                if ( $should_count && ( ( isset( $views_options['use_ajax'] ) && (int) $views_options['use_ajax'] === 0 ) || ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) ) ) {
                    update_post_meta( $id, $meta_field, $post_views + 1 );
                    do_action( $this->action_prefix . '_increment_views', $post_views + 1 );
                }
            }
        }
    }

    /**
     * @return void
     */
    public function _enqueue_scripts() {
        if ( ! $this->cache_enabled() ) {
            return;
        }

        global $user_ID, $post;
        $views_options = $this->views_options();

        if ( ! wp_is_post_revision( $post ) && ( is_single() || is_page() ) ) {
            $should_count = false;
            switch ( (int) $views_options['count'] ) {
                case 0:
                    $should_count = true;
                    break;
                case 1:
                    if ( empty( $_COOKIE[ USER_COOKIE ] ) && (int) $user_ID === 0 ) {
                        $should_count = true;
                    }
                    break;
                case 2:
                    if ( (int) $user_ID > 0 ) {
                        $should_count = true;
                    }
                    break;
            }

            $should_count = apply_filters( $this->action_prefix . '_should_count', $should_count, (int) $post->ID );
            if ( $should_count ) {
                wp_localize_script( $this->script_handle . '-scripts', 'wpshop_views_counter_params', [
                    'url'                  => admin_url( 'admin-ajax.php' ),
                    'nonce'                => wp_create_nonce( 'wpshop-nonce' ),
                    'is_postviews_enabled' => $this->is_postviews_enabled(),
                    'post_id'              => $post->ID,
                ] );
            }
        }
    }

    /**
     * @return bool
     */
    protected function cache_enabled() {
        $enabled = ( defined( 'WP_CACHE' ) && WP_CACHE ) ||
                   ( isset( $GLOBALS["wp_fastest_cache_options"] ) &&
                     isset( $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheStatus ) &&
                     $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheStatus === 'on' );

        return (bool) apply_filters( 'wpshop_core_views_counter:cache_enabled', $enabled );
    }

    /**
     * @return mixed
     */
    public function enabled() {
        return apply_filters( __METHOD__, ! $this->is_postviews_enabled() );
    }

    /**
     * @return bool
     */
    public function is_postviews_enabled() {
		// fix bug when active_plugins empty string https://github.com/wpshop-team/core/issues/77
		$active_plugins = get_option( 'active_plugins', [] );
		if ( ! is_array( $active_plugins ) ) {
			$active_plugins = [];
		}
        return in_array(
            'wp-postviews/wp-postviews.php',
            apply_filters( 'active_plugins', $active_plugins )
        );
    }

    /**
     * Special hook to prevent lost views data on remove plugin ViewsCounter
     *
     * @param string $query
     *
     * @return string
     */
    public function _filter_postviews_uninstall( $query ) {
        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return $query;
        }

        global $wpdb;

        $q = "DELETE FROM $wpdb->postmeta WHERE meta_key = 'views'";

        if ( 0 === strcasecmp( $q, $query ) ) {
            $query .= ' LIMIT 0';
        }

        return $query;
    }
}
