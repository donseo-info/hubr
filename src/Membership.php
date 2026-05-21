<?php

namespace WPShop\WPCommunity;

use DateTime;

class Membership {

    const POST_META_ACCESS = 'access';

    const ACCESS_DEFAULT = 'default';
    const ACCESS_PRIVATE = 'private';
    const ACCESS_PUBLIC  = 'public';

    /**
     * @return array
     */
    public static function get_always_accessible_pages() {
        $pages = [
            get_setting( 'page.join' ),
            get_setting( 'page.order' ),
            get_setting( 'page.profile' ),
            get_setting( 'page.offer' ),
            get_setting( 'page.payment' ),
            get_setting( 'page.contacts' ),
        ];

        return array_filter( $pages );
    }

    /**
     * @return void
     */
    public function init() {
        add_shortcode( 'member', [ $this, 'shortcode_member' ] );

        add_filter( 'the_content', [ $this, 'filter_the_content' ] );

        add_filter( 'wpcommunity/author/name', [ $this, '_append_membership_badge' ], 10, 2 );
        add_filter( 'wpcommunity/post-card/author_name', [ $this, '_append_membership_badge' ], 10, 2 );
        add_filter( 'wpcommunity/comment/author', [ $this, '_use_comment_author_by_user_preferences' ], 9, 2 );
        add_filter( 'wpcommunity/comment/author', [ $this, '_append_membership_badge' ], 10, 2 );

        add_filter( 'get_comment_author', function ( $comment_author, $comment_id, $comment ) {
            return apply_filters( 'wpcommunity/comment/author', $comment_author, $comment ? $comment->user_id : null );
        }, 10, 3 );
    }

    /**
     * @param string $name
     * @param int    $user_id
     *
     * @return string
     */
    public function _use_comment_author_by_user_preferences( $name, $user_id ) {
        /**
         * @since 1.0
         */
        $use_stored_name = apply_filters( 'wpcommunity/comment/use_store_author_name', false );

        if ( ! $use_stored_name && $user_id ) {
            if ( $username = theme_container()->get( User::class )->get_user_name( $user_id ) ) {
                $name = $username;
            }
        }

        return $name;
    }

    /**
     * @param string $name
     * @param int    $user_id
     *
     * @return mixed|string
     */
    public function _append_membership_badge( $name, $user_id ) {
        if ( is_admin() ) {
            return $name;
        }

        if ( $user_id && ! $this->is_expired( $user_id ) ) {
            $badge = '<svg width="36" height="20"><use xlink:href="#subscribe-badge"></use></svg>';

            /**
             * Allows to change membership badge
             *
             * @since 1.0
             */
            $badge = apply_filters( 'wpcommunity/membership/badge', $badge, $user_id, $name );
            $name  .= '<span class="membership-badge">' . $badge . '</span>';
        }

        return $name;
    }

    public function filter_the_content( $content ) {

        if ( ! $this->is_user_post_access() ) {
            return __( 'Sorry, you dont have permission to read this post.', 'wpcommunity' );
        }

        return $content;
    }


    public function shortcode_member( $atts, $content = null ) {
        if ( is_user_logged_in() && ! is_null( $content ) ) {
            return $content;
        }

        return '';
    }

    /**
     * @return void
     */
    public function notify_expired() {
        if ( ! get_setting( 'account.pro.enable_expire_notification' ) ) {
            return;
        }
        foreach ( $this->get_users_for_expiration_notifications( DAY_IN_SECONDS ) as $user ) {
            theme_container()->get( Mail::class )->subscription_expire_mail( $user->user_email );
            update_user_meta( $user->ID, User::USER_META_EXPIRE_NOTIFIED, current_time( 'timestamp' ) );
        }
    }

    /**
     * @param int $left_time in seconds
     *
     * @return \Generator|\WP_User[]
     */
    protected function get_users_for_expiration_notifications( $left_time ) {

        $args = [
            'orderby'     => 'ID',
            'count_total' => false,
            'number'      => 500,
            'meta_query'  => [
                'relation' => 'AND',
                [
                    'key'     => User::USER_META_EXPIRED,
                    'value'   => current_time( 'timestamp' ) + $left_time,
                    'compare' => '<=',
                ],
                [
                    'key'     => User::USER_META_EXPIRED,
                    'value'   => current_time( 'timestamp' ),
                    'compare' => '>',
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => User::USER_META_EXPIRE_NOTIFIED,
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => User::USER_META_EXPIRE_NOTIFIED,
                        'value'   => current_time( 'timestamp' ) - $left_time,
                        'compare' => '<=',
                    ],
                ],
            ],
        ];

        for ( $i = 1 ; $i < 10000000 ; $i ++ ) {
            $args['paged'] = $i;

            $user_search = new \WP_User_Query( $args );
            //var_dump( $user_search->request );

            $users = (array) $user_search->get_results();

//            global $wpdb;
//            var_dump( $wpdb->last_query );
//            die;
            if ( ! $users ) {
                return;
            }

            foreach ( $users as $user ) {
                yield $user;
            }
        }
    }

    /**
     * Получить срок истечения подписки
     *
     * @param $user_id
     *
     * @return int|false
     */
    public function get_expired( $user_id ) {
        $expired = (int) get_user_meta( $user_id, User::USER_META_EXPIRED, true );

        if ( empty( $expired ) ) {
            return false;
        }

        return $expired;
    }

    /**
     * @param int|null $user_id
     *
     * @return bool
     */
    public function is_expired( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return ! $this->get_expired( $user_id ) || $this->get_expired( $user_id ) < current_time( 'timestamp' );
    }

    public function get_expired_date( $user_id, $format = 'd.m.Y' ) {
        $expired = $this->get_expired( $user_id );

        if ( empty( $expired ) ) {
            return false;
        }

        return date( $format, $expired );
    }

    public function get_expired_days( $user_id ) {
        $expired = $this->get_expired( $user_id );

        if ( empty( $expired ) ) {
            return false;
        }

        $today        = new DateTime( 'today' );
        $expired_date = new DateTime( date( 'Y-m-d', $expired ) );
        $diff         = $today->diff( $expired_date );

        // todo Диме проверить дату включительно, тут просто добавил +1
        $days = $diff->days + 1;
        if ( $diff->invert == 1 ) {
            $days = $days * - 1;
        }

        return (int) $days;
    }


    /**
     * Обновляем подписку, если просрочился срок -- продляем с сегодняшнего дня
     *
     * @param int $user_id
     * @param int $days
     *
     * @return void
     */
    public function renew_membership( $user_id, $days ) {
        $expired = (int) get_user_meta( $user_id, User::USER_META_EXPIRED, true );

        // если нет срока окончания или он уже прошел -- ставим текущую метку
        if ( empty( $expired ) || $expired < current_time( 'timestamp' ) ) {
            $expired = current_time( 'timestamp' );
        }

        $expired = $expired + ( $days * 24 * 60 * 60 );

        update_user_meta( $user_id, User::USER_META_EXPIRED, $expired );
        delete_user_meta( $user_id, User::USER_META_EXPIRE_NOTIFIED );

        /**
         * @since 1.2
         */
        do_action( 'wpcommunity/membership/renew', $user_id, $days, $expired );
    }

    /**
     * @param int $user_id
     * @param int $days
     *
     * @return void
     */
    public function discard_membership( $user_id, $days = null ) {
        // todo handle partial discard
        delete_user_meta( $user_id, User::USER_META_EXPIRED );
        delete_user_meta( $user_id, User::USER_META_EXPIRE_NOTIFIED );

        /**
         * @since 1.2
         */
        do_action( 'wpcommunity/membership/discard', $user_id );
    }


    /**
     * Меняем количество дней в подписке -- без учета просрочена или нет. Просто добавляем и убавляем дни
     *
     * @param $user_id
     * @param $days
     *
     * @return void
     */
    public function change_membership_days( $user_id, $days ) {
        $expired = (int) get_user_meta( $user_id, User::USER_META_EXPIRED, true );

        if ( empty( $expired ) ) {
            $expired = current_time( 'timestamp' );
        }

        $expired = $expired + ( $days * 24 * 60 * 60 );

        update_user_meta( $user_id, User::USER_META_EXPIRED, $expired );
        delete_user_meta( $user_id, User::USER_META_EXPIRE_NOTIFIED );
    }


    /**
     * Платный ли юзер
     *
     * @param $user_id
     *
     * @return bool
     */
    public function is_member( $user_id ) {
        $expired = $this->get_expired( $user_id );

        if ( $expired && $expired > current_time( 'timestamp' ) ) {
            return true;
        }

        return false;
    }


    /**
     * Доступен ли пост, приватный или публичный
     * без учета пользователя, просто сам пост
     *
     * @param int|\WP_Post $post
     * @param int          $user_id
     *
     * @return bool
     */
    public function is_post_access( $post = 0, $user_id = 0 ) {
        // global post
        $post = get_post( $post );

        // если не удалось получить пост -- запрещаем доступ
        if ( empty( $post ) ) {
            return false;
        }

        if ( in_array( $post->ID, static::get_always_accessible_pages() ) ) {
            return true;
        }

        // enable access for the author
        if ( $user_id && $post->post_author == $user_id ) {
            return true;
        }

        $post_access = $this->get_post_access( $post->ID );

        if ( self::ACCESS_DEFAULT === $post_access ) {
            return $this->get_default_post_type_access( $post ) === self::ACCESS_PUBLIC;
        }

        // проверяем настройки поста
        if ( $post_access == self::ACCESS_PUBLIC ) {
            return true;
        } else if ( $post_access == self::ACCESS_PRIVATE ) {
            return false;
        }

        return false;
    }

    /**
     * @param \WP_Post|int $post
     * @param int          $user_id if 0 will treat as wp_get_current_user()
     *
     * @return bool
     * @see wp_get_current_user()
     */
    public function is_user_post_access( $post = 0, $user_id = 0 ) {

        /**
         * @since 1.0
         */
        $has_access = apply_filters( 'wpcommunity/membership/is_post_accessible', false, $post, $user_id );

        if ( $has_access ) {
            return true;
        }

        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        // get post access
        $is_access = $this->is_post_access( $post, $user_id );

        // если пост публичный -- просто возвращаем true
        // любой посетитель может его почитать
        if ( $is_access ) {
            return true;
        }

        // если пользователь не найден -- выходим
        if ( ! $user_id ) {
            return false;
        }

        // free access for admins
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        if ( is_author( $user_id ) ) {
            return true;
        }

        if ( $this->is_member( $user_id ) ) {
            return true;
        }

        return false;
    }


    /**
     * Получить у поста ТОЛЬКО доступы
     * не проверяются общие настройки сайта
     *
     * @param $post_id
     *
     * @return mixed|string
     */
    public function get_post_access( $post_id ) {
        $access = get_post_meta( $post_id, self::POST_META_ACCESS, true );

        if ( empty( $access ) ) {
            return self::ACCESS_DEFAULT;
        }

        return $access;
    }


    /**
     * Дефолтные настройки доступа пост тайпов у сайта
     *
     * @param \WP_Post $post
     *
     * @return string
     */
    public function get_default_post_type_access( \WP_Post $post ) {
        if ( $access_type = get_setting( 'content.default_access.post_type--' . $post->post_type ) ) {
            return $access_type;
        }

        return self::ACCESS_PRIVATE;
    }

}
