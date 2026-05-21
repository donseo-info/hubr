<?php

namespace WPShop\WPCommunity;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;

class Vote {

    const META_LIKES    = 'vote_likes';
    const META_DISLIKES = 'vote_dislikes';
    const META_ACTIVITY = 'vote_activity'; // $likes + $dislikes
    const META_SCORE    = 'vote_score'; // $likes - $dislikes
    const META_USERS    = 'vote_users'; // id проголосовавших
    const META_IPS      = 'vote_ips'; // ip проголосовавших

    const VOTE_METHOD_USERS = 'users';
    const VOTE_METHOD_IPS   = 'ips';

    /**
     * @return void
     */
    public function init() {

        $action = 'wpcommunity_vote_process';
        add_action( "wp_ajax_{$action}", [ $this, 'ajax_vote_process' ] );
        add_action( "wp_ajax_nopriv_{$action}", [ $this, 'ajax_vote_process' ] );

    }

    /**
     * @return array
     */
    public static function options() {
        return [
            self::VOTE_METHOD_USERS => __( 'Users', 'wpcommunity' ),
            self::VOTE_METHOD_IPS   => __( 'IPs', 'wpcommunity' ),
        ];
    }

    /**
     * @param string $object_type 'post', 'comment'
     * @param int    $object_id
     *
     * @return array
     */
    public function get_vote_data( $object_type, $object_id ) {

        if ( $object_type == 'post' ) {
            $object   = get_post( $object_id );
            $function = 'get_post_meta';
        } elseif ( $object_type == 'comment' ) {
            $object   = get_comment( $object_id );
            $function = 'get_comment_meta';
        } else {
            return [];
        }

        if ( ! $object ) {
            return [];
        }

        $likes    = (int) $function( $object_id, self::META_LIKES, true );
        $dislikes = (int) $function( $object_id, self::META_DISLIKES, true );
        $activity = (int) $function( $object_id, self::META_ACTIVITY, true );
        $score    = (int) $function( $object_id, self::META_SCORE, true );
        $users    = $function( $object_id, self::META_USERS, true );
        $ips      = $function( $object_id, self::META_IPS, true );

        if ( empty( $users ) ) {
            $users = [];
        }

        if ( empty( $ips ) ) {
            $ips = [];
        }

        return [
            'type'     => $object_type,
            'likes'    => $likes,
            'dislikes' => $dislikes,
            'activity' => $activity,
            'score'    => $score,
            'users'    => $users,
            'ips'      => $ips,
        ];
    }

    /**
     * @param string $object_type 'post', 'comment'
     * @param int    $object_id
     *
     * @return int
     */
    public function get_vote_score( $object_type, $object_id ) {
        if ( $object_type == 'post' ) {
            $function = 'get_post_meta';
        } elseif ( $object_type == 'comment' ) {
            $function = 'get_comment_meta';
        } else {
            return 0;
        }

        return (int) $function( $object_id, self::META_SCORE, true );
    }


    /**
     * @param $user_id
     *
     * @return bool|WP_Error
     */
    public function can_user_vote( $user_id ) {

        $method = $this->get_method();

        if ( self::VOTE_METHOD_USERS === $method ) {
            // todo можно какую нибудь проверку на бан или что то такое сделать
            // todo вынести в настройки и сделать возможность голосовать всем

            // если авторизован
            if ( ! is_user_logged_in() ) {
                return new WP_Error( 'login_required', __( 'Sign in to vote', 'wpcommunity' ) );
            }

            // если пользователь голосует за свой пост
            if ( get_current_user_id() == $user_id ) {
                return new WP_Error( 'cant_vote_yourself', __( 'You can\'t vote for yourself.', 'wpcommunity' ) );
            }
        }

        return true;
    }


    /**
     * Получить метод сохранения голосов, по юзерам или IP
     *
     * @return string
     */
    public function get_method() {
        return get_setting( 'likes.vote_method' );
    }

    /**
     * @return int|string current user id or ip
     */
    public function get_method_item() {
        $method = $this->get_method();

        return ( $method == self::VOTE_METHOD_USERS ) ? get_current_user_id() : $this->get_ip();
    }

    /**
     * @param string $object_type 'post', 'comment'
     * @param int    $object_id
     * @param string $direction   '+' or '-'
     *
     * @return array
     */
    public function vote_change( $object_type, $object_id, $direction = '+' ) {
        $vote = $this->get_vote_data( $object_type, $object_id );

        // для кармы направление и количество выполнений
        // тк если юзер поставил + и получил +1, жмет минус и получает -1, перескакивает через 2 балла
        // то есть и снимать и начислять нужно 2 раза
        $karma_direction = $direction;
        $karma_iteration = 1;

        // способ хранения проголосовавших
        $method       = $this->get_method();
        $method_item  = ( $method == 'users' ) ? get_current_user_id() : $this->get_ip();
        $method_value = ( $direction == '+' ) ? 1 : - 1;

        // если юзер уже голосовал
        if ( isset( $vote[ $method ][ $method_item ] ) ) {

            // если он поставил плюс
            if ( $vote[ $method ][ $method_item ] > 0 ) {

                // и жмет опять на плюс -- убираем плюс и юзера
                // else поставил плюс и жмет на минус -- забираем плюс и добавляем минус
                if ( $direction == '+' ) {
                    $karma_direction = '-';
                    $vote['likes'] --;
                    unset( $vote[ $method ][ $method_item ] );
                } else {
                    $karma_direction = '-';
                    $karma_iteration = 2;
                    $vote['likes'] --;
                    $vote['dislikes'] ++;
                    $vote[ $method ][ $method_item ] = $method_value;
                }

                // если он поставил минус
            } else {
                // и жмет опять на минус -- убираем минус и юзера
                // else поставил минус и жмет на плюс -- забираем минус и добавляем плюс
                if ( $direction == '-' ) {
                    $karma_direction = '+';
                    $vote['dislikes'] --;
                    unset( $vote[ $method ][ $method_item ] );
                } else {
                    $karma_direction = '+';
                    $karma_iteration = 2;
                    $vote['dislikes'] --;
                    $vote['likes'] ++;
                    $vote[ $method ][ $method_item ] = $method_value;
                }
            }

        } else {

            // если юзер не голосовал

            if ( $direction == '-' ) {
                $vote['dislikes'] ++;
            } else {
                $vote['likes'] ++;
            }

            $vote[ $method ][ $method_item ] = $method_value;

        }

        $vote['activity'] = $vote['likes'] + $vote['dislikes'];
        $vote['score']    = $vote['likes'] - $vote['dislikes'];


        // определяем функцию для обновления данных
        if ( $vote['type'] == 'post' ) {
            $function = 'update_post_meta';
        } elseif ( $vote['type'] == 'comment' ) {
            $function = 'update_comment_meta';
        }

        if ( isset( $function ) ) {
            $function( $object_id, self::META_LIKES, $vote['likes'] );
            $function( $object_id, self::META_DISLIKES, $vote['dislikes'] );
            $function( $object_id, self::META_ACTIVITY, $vote['activity'] );
            $function( $object_id, self::META_SCORE, $vote['score'] );
            $function( $object_id, self::META_USERS, $vote['users'] );
            $function( $object_id, self::META_IPS, $vote['ips'] );

            do_action( 'wpcommunity/vote/change', $object_type, $object_id, $karma_direction, $karma_iteration );
        }

        return $vote;
    }

    /**
     * @param \WP_Post|\WP_Comment $object
     *
     * @return void
     */
    public function the_vote( $object ) {

        // проверяем объект, пост, комментарий
        if ( $object instanceof \WP_Post ) {
            $object_type = 'post';
            $author_id   = $object->post_author;
            $score       = $this->get_vote_score( $object_type, $object->ID );
            $vote_data   = $this->get_vote_data( $object_type, $object->ID );
            $object_id   = $object->ID;
        } elseif ( $object instanceof \WP_Comment ) {
            $object_type = 'comment';
            $author_id   = $object->user_id;
            $score       = $this->get_vote_score( $object_type, $object->comment_ID );
            $vote_data   = $this->get_vote_data( $object_type, $object->comment_ID );
            $object_id   = $object->comment_ID;
        } else {
            echo '<!-- undefined object for vote -->';

            return;
        }

        $vote_attributes = [];
        $vote_classes    = [];

        $vote_attributes[] = 'data-object-type="' . $object_type . '"';
        $vote_attributes[] = 'data-object-id="' . $object_id . '"';

        $can_user_vote = $this->can_user_vote( $author_id );
        if ( is_wp_error( $can_user_vote ) ) {
            if ( $can_user_vote->get_error_code() == 'login_required' ) {
                $vote_attributes[] = 'data-tooltip="' . $can_user_vote->get_error_message() . '"';
            }
            $vote_classes[] = 'disabled';
        }

        $method      = $this->get_method();
        $method_item = $this->get_method_item();

        $minus_active = ( isset( $vote_data[ $method ][ $method_item ] ) && $vote_data[ $method ][ $method_item ] < 0 ) ? ' active' : '';
        $plus_active  = ( isset( $vote_data[ $method ][ $method_item ] ) && $vote_data[ $method ][ $method_item ] > 0 ) ? ' active' : '';


        $get_icon = function ( $type ) {
            $icons = [
                'chevron'    => [
                    'pos' => 'ico-vote-plus',
                    'neg' => 'ico-vote-minus',
                ],
                'thumb'      => [
                    'pos' => 'ico-thumb-up-regular',
                    'neg' => 'ico-thumb-down-regular',
                ],
                'heart'      => [
                    'pos' => 'ico-heart-regular',
                    'neg' => 'ico-heart-crack-regular',
                ],
                'plus_minus' => [
                    'pos' => 'ico-plus-circle-regular',
                    'neg' => 'ico-minus-circle-regular',
                ],
            ];


            if ( array_key_exists( get_setting( 'likes.icon' ), $icons ) ) {
                return $icons[ get_setting( 'likes.icon' ) ][ $type ];
            }

            return [
                       'pos' => 'ico-vote-plus',
                       'neg' => 'ico-vote-minus',
                   ][ $type ];
        };


        $items = [
            'dislike' => [
                'render' => function () use ( $minus_active, $get_icon ) {
                    echo '<div class="vote__minus js-vote-minus' . $minus_active . '">';
                    echo '  <svg width="18" height="18"><use xlink:href="#' . $get_icon( 'neg' ) . '"></use></svg>';
                    echo '</div>';
                },
                'order'  => 10,
            ],
            'score'   => [
                'render' => function () use ( $score ) {
                    echo '<div class="vote__score js-vote-score">';
                    echo $score;
                    echo '</div>';
                },
                'order'  => 20,
            ],
            'like'    => [
                'render' => function () use ( $plus_active, $get_icon ) {
                    echo '<div class="vote__plus js-vote-plus' . $plus_active . '">';
                    echo '  <svg width="18" height="18"><use xlink:href="#' . $get_icon( 'pos' ) . '"></use></svg>';
                    echo '</div>';
                },
                'order'  => 30,
            ],
        ];

        $orders = [
            'dislike' => 10,
            'score'   => 20,
            'like'    => 30,
        ];

        if ( ! get_setting( 'likes.show_dislikes' ) ) {
            unset( $items['dislike'] );
            // move score after like button
            $items['score']['order'] = 40;
            $orders['score']         = 40;
        }
        if ( ! get_setting( 'likes.show_score' ) ) {
            unset( $items['score'] );
        }

        /**
         * @since 1.3.0
         */
        $orders = (array) apply_filters( 'wpcommunity/the_vote/orders', $orders );
        foreach ( $orders as $_k => $_v ) {
            if ( array_key_exists( $_k, $items ) ) {
                $items[ $_k ]['order'] = $_v;
            }
        }

        uasort( $items, function ( $item1, $item2 ) {
            return $item1['order'] - $item2['order'];
        } );

        echo '<div class="post-card-right__vote vote js-vote-container ' . implode( ' ', $vote_classes ) . '" ' . implode( ' ', $vote_attributes ) . '>';
        foreach ( $items as $item ) {
            $item['render']();
        }
        echo '</div>';
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function ajax_vote_process() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! $this->can_user_vote( get_current_user_id() ) ) {
            wp_send_json_error( new WP_Error( 'cant_vote', __( 'Sign in to vote', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['object_id'] ) || empty( $_REQUEST['object_type'] ) || empty( $_REQUEST['direction'] ) ) {
            wp_send_json_error( new WP_Error( 'wrong_data', __( 'Wrong data', 'wpcommunity' ) ) );
        }

        $object_type = $_REQUEST['object_type'];
        $object_id   = (int) $_REQUEST['object_id'];

        $object = false;

        if ( $object_type == 'post' ) {
            $object = get_post( $object_id );
        } elseif ( $object_type == 'comment' ) {
            $object = get_comment( $object_id );
        }

        if ( ! $object ) {
            wp_send_json_error( new WP_Error( 'object_not_found', __( 'Object not found', 'wpcommunity' ) ) );
        }

        if ( $_REQUEST['direction'] == 'minus' ) {
            $vote = $this->vote_change( $object_type, $object_id, '-' );
        } else {
            $vote = $this->vote_change( $object_type, $object_id, '+' );
        }

        wp_send_json_success( [ 'score' => $vote['score'] ] );

    }

    /**
     * Get IP
     *
     * @return mixed
     */
    public function get_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
