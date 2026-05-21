<?php

namespace WPShop\WPCommunity\Features;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WP_Post;

class ViewsCounter {

    const META_VIEWS = 'views';

    const COUNT_ALL    = 'all';
    const COUNT_USERS  = 'users';
    const COUNT_UNIQUE = 'unique';

    /**
     * @return void
     */
    public function init() {
        add_action( 'wpcommunity/assets/enqueue_scripts', [ $this, '_enqueue_scripts' ] );
        add_action( 'wp_head', [ $this, '_process_views' ] );

        $action = 'wpcommunity_views_counter';
        add_action( "wp_ajax_{$action}", [ $this, '_increment_views_ajax' ] );
        add_action( "wp_ajax_nopriv_{$action}", [ $this, '_increment_views_ajax' ] );
    }

    /**
     * @return void
     */
    public function _enqueue_scripts() {
        if ( ! $this->cache_enabled() ) {
            return;
        }

        global $user_ID, $post;
        $views_options = $this->get_views_options();

        if ( ! $views_options['enabled'] ) {
            return;
        }

        wp_register_script(
            'wpcommunity-views-counter',
            get_template_directory_uri() . '/assets/public/js/views-counter.min.js',
            [],
            '1.0',
            true
        );

        $should_count = false;

        if ( ! wp_is_post_revision( $post ) && ( is_single() || is_page() ) ) {
            switch ( $views_options['how_count'] ) {
                case self::COUNT_ALL:
                    $should_count = true;
                    break;
                case self::COUNT_UNIQUE:
                    if ( empty( $_COOKIE[ USER_COOKIE ] ) && (int) $user_ID === 0 ) {
                        $should_count = true;
                    }
                    break;
                case self::COUNT_USERS:
                    if ( (int) $user_ID > 0 ) {
                        $should_count = true;
                    }
                    break;
                default:
                    break;
            }
        }

        if ( $should_count ) {
            wp_localize_script( 'wpcommunity-scripts', 'wpcommunity_views_counter_params', [
                'url'     => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wpshop-nonce' ),
                'post_id' => $post->ID,
            ] );
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

        return (bool) apply_filters( 'wpcommunity/views/cache_enabled', $enabled );
    }

    /**
     * @param WP_Post|int $post
     *
     * @return void
     */
    public function the_views( $post = null ) {
        if ( $this->cache_enabled() ) {
            $post = get_post( $post );
            echo '<span class="post-meta__views-number js-views-number" data-post_id="' . $post->ID . '">' . $this->get_the_views( $post ) . '</span>';
        } else {
            echo '<span class="post-meta__views-number">' . $this->get_the_views( $post ) . '</span>';
        }

    }

    /**
     * @param WP_Post|int $post
     *
     * @return int|string
     */
    public function get_the_views( $post = null ) {
        $post = get_post( $post );
        if ( ! $post ) {
            return '';
        }

        $post_views = (int) get_post_meta( $post->ID, self::META_VIEWS, true );

        /**
         * Allows to modify output of post views
         *
         * [ru] Позволяет поменять вывод просмотра постов
         *
         * @hooked \WPShop\WPCommunity\DefaultHooks::_round_views_count()
         *
         * @since 1.1
         */
        $post_views = apply_filters( 'wpcommunity/views_counter/post_views', $post_views, $post );

        return $post_views;
    }

    /**
     * @return array{'enabled':bool,'exclude_bots':bool,'how_count':string}
     */
    protected function get_views_options() {
        /**
         * @since 1.0
         */
        $options = apply_filters( 'wpcommunity/views/options', [] );
        $options = wp_parse_args( $options, [
            'enabled'      => 1,
            'how_count'    => self::COUNT_ALL,
            'exclude_bots' => 1,
        ] );

        return $options;
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

        if ( ! wp_is_post_revision( $post ) && ! is_preview() ) {
            if ( is_single() || is_page() ) {
                $views_options = $this->get_views_options();

                $id           = $post->ID;
                $should_count = false;

                switch ( $views_options['how_count'] ) {
                    case self::COUNT_ALL:
                        $should_count = true;
                        break;
                    case self::COUNT_UNIQUE:
                        if ( empty( $_COOKIE[ USER_COOKIE ] ) && (int) $user_ID === 0 ) {
                            $should_count = true;
                        }
                        break;
                    case self::COUNT_USERS:
                        if ( (int) $user_ID > 0 ) {
                            $should_count = true;
                        }
                        break;
                    default:
                        break;
                }

                if ( $views_options['exclude_bots'] ) {
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

                /**
                 * @since 1.0
                 */
                $should_count = apply_filters( 'wpcommunity/views/should_count', $should_count, $id );

                if ( $should_count ) {
                    $this->increment_views( $id );
                }
            }
        }
    }

    /**
     * @param WP_Post|int $post
     *
     * @return WP_Error|int
     */
    public function increment_views( $post ) {
        $post = get_post( $post );

        if ( ! $post ) {
            return new WP_Error( 'views_counter', __( 'Unable to update the number of views of the wrong post', 'wpcommunity' ) );
        }

        /**
         * @since 1.0
         */
        $meta_field = apply_filters( 'wpcommunity/views/meta_field', self::META_VIEWS );

        if ( metadata_exists( 'post', $post->ID, $meta_field ) ) {
            $count = get_post_meta( $post->ID, $meta_field, true );
        } else {
            /**
             * @since 1.0
             */
            $count = apply_filters( 'wpcommunity/views/initial_value', 0 );
        }

        $count = absint( $count );
        $count ++;

        update_post_meta( $post->ID, $meta_field, $count );

        return $count;
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _increment_views_ajax() {
        $request = wp_parse_args( $_REQUEST, [ 'id' => 0 ] );

        if ( is_wp_error( $count = $this->increment_views( $request['id'] ) ) ) {
            wp_send_json_error( $count );
        }

        wp_send_json_success( [
            [
                'id'    => $request['id'],
                'count' => $count,
            ],
        ] );
    }


}
