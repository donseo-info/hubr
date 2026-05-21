<?php

namespace WPShop\WPCommunity;

use WP_Comment;
use WP_Post;
use WP_User;
use WPShop\WPCommunity\Database\Subs;

class User {

    const USER_META_EXPIRED              = 'expired';
    const USER_META_EXPIRE_NOTIFIED      = 'expire_notified';
    const USER_META_SUBSCRIPTION_HISTORY = 'subscription_history';
    const USER_META_TELEGRAM_USER_ID     = 'telegram_user_id';
    const USER_META_TELEGRAM_USERNAME    = 'telegram_username';

    const USER_META_AVATAR_ATTACHMENT      = '_avatar_attachment_id';
    const USER_META_AVATAR_ATTACHMENT_BLOG = '_avatar_attachment_blog_id';

    const FOLLOW_TYPE_USER     = 'user';
    const FOLLOW_TYPE_CATEGORY = 'term:category';
    const FOLLOW_TYPE_TAG      = 'term:tag';

    /**
     * @return void
     */
    public function init() {

        // author base url
        add_action( 'init', [ $this, 'change_author_base_url' ] );
        if ( ! has_action( 'after_switch_theme', 'flush_rewrite_rules' ) ) {
            add_action( 'after_switch_theme', 'flush_rewrite_rules' );
        }

        // meta fields
        add_action( 'show_user_profile', [ $this, '_add_extra_profile_fields' ] );
        add_action( 'edit_user_profile', [ $this, '_add_extra_profile_fields' ] );
        add_action( 'personal_options_update', [ $this, '_save_extra_user_profile_fields' ] );
        add_action( 'edit_user_profile_update', [ $this, '_save_extra_user_profile_fields' ] );

        add_filter( 'nav_menu_item_title', [ $this, '_replace_profile_title' ], 10, 2 );
        add_filter( 'wp_nav_menu_objects', [ $this, '_append_logout_link' ], 10, 2 );

        add_filter( 'show_admin_bar', function ( $show ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return false;
            }

            return $show;
        } );

        add_filter( 'get_avatar', [ $this, '_replace_avatar' ], 10, 6 );

        add_action( 'deleted_user', function ( $id ) {
            $subs = theme_container()->get( Subs::class );
            $subs->clear_subs( $id );
            $subs->clear_targets( self::FOLLOW_TYPE_USER, $id );
        } );
        add_action( 'delete_category', function ( $id ) {
            theme_container()->get( Subs::class )->clear_targets( self::FOLLOW_TYPE_CATEGORY, $id );
        } );

        add_filter( 'user_contactmethods', [ $this, '_add_social_profile_links' ], 11 );

        add_filter( 'wpcommunity/user/avatar_alt', [ $this, '_set_avatar_alt' ], 10, 2 );
    }

    /**
     * @param string $alt
     * @param int    $user_id
     *
     * @return string
     */
    public function _set_avatar_alt( $alt, $user_id ) {
        if ( ! $alt ) {
            $alt = get_user_name( $user_id );
        }

        return $alt;
    }

    /**
     * @param string                     $avatar
     * @param WP_User|WP_Comment|WP_Post $id_or_email
     * @param int                        $size
     * @param string                     $default
     * @param string                     $alt
     * @param array                      $args
     *
     * @return string
     * @see get_avatar()
     */
    public function _replace_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
        $user_id = 0;
        if ( $id_or_email instanceof WP_User ) {
            $user_id = $id_or_email->ID;
        } else if ( $id_or_email instanceof WP_Post ) {
            $user_id = $id_or_email->post_author;
        } else if ( $id_or_email instanceof WP_Comment ) {
            $user_id = $id_or_email->user_id;
        } else if ( is_numeric( $id_or_email ) ) {
            $user_id = $id_or_email;
        } else if ( is_string( $id_or_email ) ) {
            if ( $user = get_user_by( 'email', $id_or_email ) ) {
                $user_id = $user->ID;
            }
        } else {
            return $avatar;
        }

        if ( $user_id ) {
            /**
             * @since 1.1
             */
            $alt = (string) apply_filters( 'wpcommunity/user/avatar_alt', $alt, $user_id, $args );

            $attachment_id = get_user_meta( $user_id, self::USER_META_AVATAR_ATTACHMENT, true );
            if ( $attachment_id ) {
                $class = [ 'avatar', 'avatar-' . (int) $args['size'], 'photo' ];

                if ( ! $args['found_avatar'] || $args['force_default'] ) {
                    $class[] = 'avatar-default';
                }

                if ( $args['class'] ) {
                    if ( is_array( $args['class'] ) ) {
                        $class = array_merge( $class, $args['class'] );
                    } else {
                        $class[] = $args['class'];
                    }
                }

                $url = $this->get_avatar_attachment_url( $user_id );

                return sprintf( '<img src="%s" alt="%s" width="%s" height="%s" class="%s">', $url, $alt, $size, $size, implode( ' ', $class ) );
            } else {
                if ( $alt ) {
                    $avatar = str_replace( 'alt=""', 'alt="' . $alt . '"', $avatar );
                    $avatar = str_replace( 'alt=\'\'', 'alt=\'' . $alt . '\'', $avatar );
                }
            }
        }

        return $avatar;
    }

    /**
     * @param int $user_id
     *
     * @return string
     */
    protected function get_avatar_attachment_url( $user_id ) {
        $attachment_id      = get_user_meta( $user_id, self::USER_META_AVATAR_ATTACHMENT, true );
        $attachment_blog_id = null;

        if ( is_multisite() ) {
            $attachment_blog_id = get_user_meta( $user_id, self::USER_META_AVATAR_ATTACHMENT_BLOG, true );

            /**
             * @since 1.0
             */
            $attachment_blog_id = apply_filters( 'wpcommunity/user/attachment_blog_id', $attachment_blog_id, $user_id );
        }

        $attachment_blog_id && switch_to_blog( $attachment_blog_id );

        $url = wp_get_attachment_url( $attachment_id );

        $attachment_blog_id && restore_current_blog();

        return $url;
    }

    /**
     * @return void
     */
    public function change_author_base_url() {
        $author_base = 'user';

        // меняем базу автора
        $GLOBALS['wp_rewrite']->author_base = $author_base;

        // расширяем регулярку для author_name: разрешаем точку
//        add_rewrite_tag( '%author%', '([^/]+)', 'author_name=' );
//
//        if ( ! get_option( 'wpcommunity--flushed-rewrites', 0 ) ) {
//            update_option( 'wpcommunity--flushed-rewrites', 1 );
//            flush_rewrite_rules();
//        }
    }

    /**
     * @param int|WP_User|null $user_id
     *
     * @return string|null
     * @deprecated
     * @see get_user_name()
     */
    public function get_user_name( $user_id = null ) {
        if ( null === $user_id ) {
            if ( ! is_user_logged_in() ) {
                return null;
            }
            $user_id = wp_get_current_user();
        }

        if ( $user_id instanceof WP_User ) {
            $user = $user_id;
        } else {
            $user = get_user_by( 'ID', $user_id );
        }

        if ( ! $user ) {
            return null;
        }

        switch ( get_user_meta( $user->ID, 'wpcommunity_display_name', true ) ) {
            case 'nickname':
                return $user->user_login;
            case 'first_last_name':
            default:
                $first_name = get_user_meta( $user->ID, 'first_name', true );
                $last_name  = get_user_meta( $user->ID, 'last_name', true );

                return trim( "$first_name $last_name" ) ?: $user->user_login;
        }
    }

    public function get_bot_token( $user_id, $bot_provider = 'telegram' ) {
        $expired = (int) get_user_meta( $user_id, 'bot_token_expired_' . $bot_provider, true );

        // если срок токена истек -- генерируем новый
        if ( $expired < time() ) {
            return $this->generate_bot_token( $user_id, $bot_provider );
        } else {
            return get_user_meta( $user_id, 'bot_token_' . $bot_provider, true );
        }
    }

    public function generate_bot_token( $user_id, $bot_provider = 'telegram' ) {
        $token = md5( $user_id . uniqid( '', true ) );
        update_user_meta( $user_id, 'bot_token_expired_' . $bot_provider, time() + ( 60 * 60 * 24 * 7 ) );
        update_user_meta( $user_id, 'bot_token_' . $bot_provider, $token );

        return $token;
    }


    /**
     * @param WP_User $user
     *
     * @return void
     */
    public function _add_extra_profile_fields( $user ) {
        if ( ! current_user_can( 'administrator' ) ) {
            return;
        }

        $membership = theme_container()->get( Membership::class );

        $expired = $membership->get_expired( $user->ID );
        if ( ! empty( $expired ) ) {
            $expired = date( 'Y-m-d', $expired );
        }

        $invite_history = get_user_meta( $user->ID, Invite::USER_META_INVITE_HISTORY, true );

        echo '<h2>' . __( 'Membership', 'wpcommunity' ) . '</h2>';

        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';

        echo '<tr>';
        echo '  <th><label for="expired">' . __( 'Expire', 'wpcommunity' ) . '</label></th>';
        echo '  <td>';
        echo '  <input type="date" name="expired" id="expired" value="' . esc_attr( $expired ) . '" class="regular-text">';
        echo '  Days: ' . $membership->get_expired_days( $user->ID );
        echo '  <p class="description">' . __( 'Subscription expiration date, format: Y-m-d', 'wpcommunity' ) . '</p>';

        echo '  </td>';
        echo '</tr>';

        echo '<tr>';
        echo '  <th><label for="expired">' . __( 'Invite activations', 'wpcommunity' ) . '</label></th>';
        echo '  <td>';

        if ( ! empty( $invite_history ) ) {
            echo '<table>';
            foreach ( $invite_history as $invite_date => $invite_post_id ) {
                $invite_post = get_post( $invite_post_id );
                echo '<tr>';
                echo '<td><a href="' . get_edit_post_link( $invite_post_id ) . '" target="_blank">' . $invite_post_id . '</a></td>';
                echo '<td>';
                echo( $invite_post ? $invite_post->post_name : '--' );
                echo '</td>';
                echo '<td>';
                echo date( 'd.m.Y H:i', $invite_date );
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }

        echo '  </td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

    }


    /**
     * @param int $user_id
     *
     * @return void
     */
    public function _save_extra_user_profile_fields( $user_id ) {
        if ( ! current_user_can( 'administrator' ) ) {
            return;
        }

        $expired = strtotime( trim( $_POST['expired'] ) );
        update_user_meta( $user_id, self::USER_META_EXPIRED, $expired );
        delete_user_meta( $user_id, self::USER_META_EXPIRE_NOTIFIED );

    }

    /**
     * @param string    $title
     * @param \stdClass $menu_item
     *
     * @return string
     */
    public function _replace_profile_title( $title, $menu_item ) {
        if ( $menu_item->type === 'post_type' &&
             $menu_item->object === 'page' &&
             $menu_item->object_id == get_setting( 'page.profile' )
        ) {
            if ( is_user_logged_in() ) {
                $title .= ' (' . $this->get_user_name() . ')';
            } else {
                $title = __( 'Sign In', 'wpcommunity' );
            }
        }

        return $title;
    }

    /**
     * @param array     $items
     * @param \stdClass $args
     *
     * @return array
     */
    public function _append_logout_link( $items, $args ) {
        if ( $args->theme_location !== 'primary-menu' ) {
            return $items;
        }
        if ( ! is_user_logged_in() ) {
            return $items;
        }

        $id = array_reduce( $items, function ( $carry, $item ) {
            return $item->ID > $carry ? $item->ID : $carry;
        }, 0 );

        /**
         * @since 1.0
         */
        $logout_item = apply_filters( 'wpcommunity/user/logout_menu_item', [
            'ID'      => $id,
            'db_id'   => $id,
            'title'   => __( 'Log Out', 'wpcommunity' ),
            'url'     => wp_logout_url( home_url() ),
            'type'    => 'custom',
            'target'  => null,
            'current' => null,
            'xfn'     => null,
        ] );

        return array_merge( $items, [ (object) $logout_item ] );
    }

    /**
     * @param array $methods
     *
     * @return array
     */
    public function _add_social_profile_links( $methods ) {
        $services = theme_container()->get( Social::class )->get_services();

        foreach ( $services as $key => $item ) {
            if ( ! array_key_exists( $key, $methods ) ) {
                $methods[ $key ] = sprintf( __( '%s profile URL', 'wpcommunity' ), $item['label'] );
            }
        }

        return $methods;
    }
}
