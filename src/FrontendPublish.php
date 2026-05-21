<?php

namespace WPShop\WPCommunity;

use WP_Error;
use WP_Post;
use WP_Query;

class FrontendPublish {

    const POST_META_EDIT_EXPIRE = 'edit_expire';

    /**
     * @var array
     */
    protected $formats;

    /**
     * Constructor
     */
    public function __construct() {
        $this->formats = [
            'post'     => _x( 'Post', 'post_formats', 'wpcommunity' ),
            'question' => _x( 'Question', 'post_formats', 'wpcommunity' ),
            'video'    => _x( 'Video', 'post_formats', 'wpcommunity' ),
            'link'     => _x( 'Link', 'post_formats', 'wpcommunity' ),
            'event'    => _x( 'Event', 'post_formats', 'wpcommunity' ),
        ];
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'save_post', [ $this, '_save_edit_expiration' ] );
    }

    /**
     * @param WP_Post|int $post
     *
     * @return bool
     */
    public function can_edit( $post ) {
        if ( $post instanceof WP_Post ) {
            $post = $post->ID;
        }

        $edit_expired = get_post_meta( $post, self::POST_META_EDIT_EXPIRE, true );

        if ( $edit_expired ) {
            return $edit_expired > current_time( 'timestamp' );
        }

        return true;
    }

    /**
     * @param int $post_id
     *
     * @return void
     */
    public function _save_edit_expiration( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! get_setting( 'publish.edit_time' ) ) {
            return;
        }

        if ( get_post_status( $post_id ) != 'publish' ) {
            return;
        }

        update_post_meta(
            $post_id,
            self::POST_META_EDIT_EXPIRE,
            current_time( 'timestamp' ) + get_setting( 'publish.edit_time' )
        );
    }

    public function is_post_editable( $post_id, $user_id ) {
        $post = get_post( $post_id );

        $errors = new WP_Error();

        if ( ! $post instanceof WP_Post ) {
            $errors->add( 'not_a_post', __( 'You are trying to edit not a post.', 'wpcommunity' ) );
        } elseif ( $post->post_author != $user_id ) {
            $errors->add( 'someone_else_post', __( 'You don\'t have access to this post.', 'wpcommunity' ) );
        } elseif ( $post->post_type != 'post' ) {
            $errors->add( 'incorrect_post_type', __( 'Incorrect post type', 'wpcommunity' ) );
        } elseif ( ! in_array( $post->post_status, [ 'publish', 'future', 'pending', 'draft', 'private' ] ) ) {
            $errors->add( 'incorrect_post_status', __( 'Incorrect post status', 'wpcommunity' ) );
        } elseif ( wp_is_post_revision( $post ) ) {
            $errors->add( 'post_revision', __( 'It\'s not a correct post.', 'wpcommunity' ) );
        } elseif ( ! $this->can_edit( $post ) ) {
            $errors->add( 'post_revision', __( 'Edit time is expired.', 'wpcommunity' ) );
        }

        if ( $errors->has_errors() ) {
            return $errors;
        }

        return true;
    }

    public function is_post_deletable( $post_id, $user_id ) {
        $post = get_post( $post_id );

        $errors = new WP_Error();

        if ( ! $post instanceof WP_Post ) {
            $errors->add( 'not_a_post', __( 'You are trying to delete not a post.', 'wpcommunity' ) );
        } elseif ( $post->post_author != $user_id ) {
            $errors->add( 'someone_else_post', __( 'You don\'t have access to this post.', 'wpcommunity' ) );
        } elseif ( $post->post_type != 'post' ) {
            $errors->add( 'incorrect_post_type', __( 'Incorrect post type', 'wpcommunity' ) );
        } elseif ( ! in_array( $post->post_status, [ 'pending', 'draft', ] ) ) {
            $errors->add( 'incorrect_post_status', __( 'Incorrect post status', 'wpcommunity' ) );
        } elseif ( wp_is_post_revision( $post ) ) {
            $errors->add( 'post_revision', __( 'It\'s not a correct post.', 'wpcommunity' ) );
        }

        // todo разрешаем удалять только пост статусы pending и draft

        if ( $errors->has_errors() ) {
            return $errors;
        }

        return true;
    }


    public function get_drafts( $user_id ) {

        $posts = get_posts( [
            'author'         => $user_id,
            'post_status'    => 'draft',
            'posts_per_page' => - 1,
        ] );

        return $posts;

    }

    /**
     * @param int|null $user_id
     *
     * @return WP_Query
     */
    public function get_drafts_query( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        $query   = new WP_Query( [
            'author'         => $user_id,
            'post_type'      => 'post',
            'post_status'    => 'draft',
            'posts_per_page' => 50,
        ] );

        return $query;
    }

    /**
     * @param int|null $user_id
     *
     * @return WP_Query
     */
    public function get_pending_query( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        $query   = new WP_Query( [
            'author'         => $user_id,
            'post_type'      => 'post',
            'post_status'    => 'pending',
            'posts_per_page' => 50,
        ] );

        return $query;
    }

    /**
     * @param int|null $user_id
     *
     * @return WP_Query
     */
    public function get_published_query( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        $query   = new WP_Query( [
            'author'         => $user_id,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
        ] );

        return $query;
    }

    public function get_edit_link( $post_id ) {

        $page_publish = (int) get_setting( 'page.publish' );
        $edit_link    = get_the_permalink( $page_publish );

        $edit_link = add_query_arg( [
            'id' => $post_id,
        ], $edit_link );

        return $edit_link;

    }

    /**
     * @return array
     */
    public function get_formats() {
        /**
         * @since 1.3.0
         */
        $formats = (array) apply_filters( 'wpcommunity/frontend_publish/formats', $this->formats );

        return $formats;
    }


    /**
     * @param int|null $thumbnail_id
     * @param int|null $post
     *
     * @return string
     * @see _wp_post_thumbnail_html()
     */
    function get_post_thumbnail_html( $thumbnail_id = null, $post = null ) {
        $_wp_additional_image_sizes = wp_get_additional_image_sizes();

        $post               = get_post( $post );
        $post_type_object   = get_post_type_object( $post->post_type );
        $set_thumbnail_link = '<p class="hide-if-no-js"><a href="%s" id="set-post-thumbnail"%s class="thickbox">%s</a></p>';
        $upload_iframe_src  = $this->get_upload_iframe_src( 'image', $post->ID );

        $content = sprintf(
            $set_thumbnail_link,
            esc_url( $upload_iframe_src ),
            '', // Empty when there's no featured image set, `aria-describedby` attribute otherwise.
            esc_html( $post_type_object->labels->set_featured_image )
        );

        if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
            $size = isset( $_wp_additional_image_sizes['post-thumbnail'] ) ? 'post-thumbnail' : [ 266, 266 ];

            /**
             * Filters the size used to display the post thumbnail image in the 'Featured image' meta box.
             *
             * Note: When a theme adds 'post-thumbnail' support, a special 'post-thumbnail'
             * image size is registered, which differs from the 'thumbnail' image size
             * managed via the Settings > Media screen.
             *
             * @param string|int[] $size         Requested image size. Can be any registered image size name, or
             *                                   an array of width and height values in pixels (in that order).
             * @param int          $thumbnail_id Post thumbnail attachment ID.
             * @param WP_Post      $post         The post object associated with the thumbnail.
             *
             * @since 1.0
             *
             */
            $size = apply_filters( 'wpcommunity/post_thumbnail_html/size', $size, $thumbnail_id, $post );

            $thumbnail_html = wp_get_attachment_image( $thumbnail_id, $size );

            if ( ! empty( $thumbnail_html ) ) {
                $content = sprintf(
                    $set_thumbnail_link,
                    esc_url( $upload_iframe_src ),
                    ' aria-describedby="set-post-thumbnail-desc"',
                    $thumbnail_html
                );
                $content .= '<p class="hide-if-no-js howto" id="set-post-thumbnail-desc">' . __( 'Click the image to edit or update' ) . '</p>';
                $content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">' . esc_html( $post_type_object->labels->remove_featured_image ) . '</a></p>';
            }
        }

        $content .= '<input type="hidden" id="_thumbnail_id" name="_thumbnail_id" value="' . esc_attr( $thumbnail_id ? $thumbnail_id : '-1' ) . '" />';

        /**
         * Filters the admin post thumbnail HTML markup to return.
         *
         * @param string   $content      Admin post thumbnail HTML markup.
         * @param int      $post_id      Post ID.
         * @param int|null $thumbnail_id Thumbnail attachment ID, or null if there isn't one.
         *
         * @since 1.0
         */
        return apply_filters( 'wpcommunity/post_thumbnail_html/html', $content, $post->ID, $thumbnail_id );
    }

    /**
     * @param string $type    Media type.
     * @param int    $post_id Post ID.
     * @param string $tab     Media upload tab.
     *
     * @return string
     * @see get_upload_iframe_src()
     */
    protected function get_upload_iframe_src( $type = null, $post_id = null, $tab = null ) {
        global $post_ID;

        if ( empty( $post_id ) ) {
            $post_id = $post_ID;
        }

        $upload_iframe_src = add_query_arg( 'post_id', (int) $post_id, admin_url( 'media-upload.php' ) );

        if ( $type && 'media' !== $type ) {
            $upload_iframe_src = add_query_arg( 'type', $type, $upload_iframe_src );
        }

        if ( ! empty( $tab ) ) {
            $upload_iframe_src = add_query_arg( 'tab', $tab, $upload_iframe_src );
        }

        /**
         * Filters the upload iframe source URL for a specific media type.
         *
         * The dynamic portion of the hook name, `$type`, refers to the type
         * of media uploaded.
         *
         * Possible hook names include:
         *
         *  - `image_upload_iframe_src`
         *  - `media_upload_iframe_src`
         *
         * @param string $upload_iframe_src The upload iframe source URL.
         *
         * @since 3.0.0
         *
         */
        $upload_iframe_src = apply_filters( "{$type}_upload_iframe_src", $upload_iframe_src );

        return add_query_arg( 'TB_iframe', true, $upload_iframe_src );
    }
}
