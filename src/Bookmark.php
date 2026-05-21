<?php

namespace WPShop\WPCommunity;

use WP_Error;

class Bookmark {

    const USER_META_BOOKMARKS = 'bookmarks';
    const POST_META_BOOKMARKS = 'bookmarks';

    /**
     * @return void
     * @deprecated
     */
    public function init() {
        // bookmarks page
//        add_action( 'pre_get_posts', [ $this, 'bookmarks_pre_get_posts' ] );
    }


    /**
     * Количество добавлений в закладки у поста
     *
     * @param $post_id
     *
     * @return int|string
     */
    public function get_post_bookmarks_count( $post_id ) {
        $count = count( $this->get_post_bookmarks( $post_id ) );

        // не выводим 0, чтобы не расстраивать участников
        if ( $count == 0 ) {
            $count = '';
        }

        return $count;
    }


    /**
     * Получить список, кто добавил в закладки пост в закладки
     *
     * @param $post_id
     *
     * @return array|mixed
     */
    public function get_post_bookmarks( $post_id ) {
        $bookmarks = get_post_meta( $post_id, self::POST_META_BOOKMARKS, true );
        if ( empty( $bookmarks ) ) {
            $bookmarks = [];
        }

        return $bookmarks;
    }


    /**
     * Получить закладки пользователя
     *
     * @param int $user_id
     *
     * @return array
     */
    public function get_bookmarks( $user_id ) {
        $bookmarks = get_user_meta( $user_id, self::USER_META_BOOKMARKS, true );
        if ( empty( $bookmarks ) ) {
            $bookmarks = [];
        }

        return (array) $bookmarks;
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function get_bookmark_post_ids( $user_id ) {
        $bookmarks = $this->get_bookmarks( $user_id );

        $ids = array_map( function ( $item ) {
            return $item['post_id'];
        }, $bookmarks );

        return array_unique( $ids );
    }


    /**
     * Сохранить закладку
     *
     * @param $user_id
     * @param $post_id
     *
     * @return int
     */
    public function save_bookmark( $user_id, $post_id ) {
        $user_bookmarks = $this->get_bookmarks( $user_id );
        $post_bookmarks = $this->get_post_bookmarks( $post_id );

        // если уже есть -- удаляем
        // если нет -- добавляем
        if ( isset( $user_bookmarks[ $post_id ] ) ) {
            unset( $user_bookmarks[ $post_id ] );
            unset( $post_bookmarks[ $user_id ] );
        } else {
            $user_bookmarks[ $post_id ] = [
                'time'    => current_time( 'timestamp' ),
                'post_id' => $post_id,
            ];
            $post_bookmarks[ $user_id ] = [
                'time'    => current_time( 'timestamp' ),
                'user_id' => $user_id,
            ];
        }

        update_user_meta( $user_id, self::USER_META_BOOKMARKS, $user_bookmarks );
        update_post_meta( $post_id, self::POST_META_BOOKMARKS, $post_bookmarks );

        // возвращаем количество букмарков у поста
        return count( $post_bookmarks );
    }

    /**
     * Добавил ли пользователь закладки
     *
     * @param $post_id
     *
     * @return false|mixed
     */
    public function is_user_bookmarked_post( $post_id ) {

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return false;
        }

        $user_bookmarks = $this->get_bookmarks( $user_id );
        if ( isset( $user_bookmarks[ $post_id ] ) ) {
            return $user_bookmarks[ $post_id ];
        }

        return false;

    }

    /**
     * @return bool
     */
    public function can_user_bookmark() {

        // todo можно какую нибудь проверку на бан или что то такое сделать
        // todo вынести в настройки и сделать возможность голосовать всем
        if ( is_user_logged_in() ) {
            return true;
        }

        return false;
    }

    /**
     * @param \WP_Query $query
     *
     * @return void
     */
    public function bookmarks_pre_get_posts( $query ) {

        // если пользователь не авторизован -- выходим
        if ( get_current_user_id() == 0 ) {
            return;
        }

        // проверяем задана ли опция страницы Закладки
        // если нет -- выходим
        $bookmark_page_id = get_setting( 'page.bookmarks' );

        if ( empty( $bookmark_page_id ) ) {
            return;
        }

        // ищем запись, если не нашли -- выходим
        $page = get_post( $bookmark_page_id );
        if ( empty( $page ) ) {
            return;
        }

        // получаем количество постов на странице из настроек
        $default_posts_per_page = get_option( 'posts_per_page' );

        if ( $page->post_name === $query->get( 'pagename' ) && ! is_admin() && $query->is_main_query() ) {

            $user_bookmarks = $this->get_bookmarks( get_current_user_id() );
            $post_ids       = [];
            foreach ( $user_bookmarks as $user_bookmark ) {
                $post_ids[] = $user_bookmark['post_id'];
            }

            if ( empty( $post_ids ) ) {
                $post_ids[] = 0;
            }

            $query->set( 'post_type', 'post' );  // override 'post_type'
            $query->set( 'pagename', null );  // override 'pagename'
            $query->set( 'posts_per_page', $default_posts_per_page );

            $query->set( 'post__in', $post_ids );

            // Support for paging
            $query->is_singular = 0;

            // custom page template
            add_filter( 'template_include', [ $this, 'bookmarks_template_include' ], 99 );
        }
    }

    public function bookmarks_template_include( $template ) {
        $target_tpl = 'template-bookmarks.php'; // EDIT to your needs

        remove_filter( 'template_include', [ $this, 'bookmarks_template_include' ], 99 );

        $new_template = locate_template( [ $target_tpl ] );

        if ( ! empty( $new_template ) ) {
            $template = $new_template;
        };

        return $template;
    }


}
