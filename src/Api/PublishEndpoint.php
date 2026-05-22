<?php

namespace WPShop\WPCommunity\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class PublishEndpoint {

    const NAMESPACE = 'hubr/v1';
    const ROUTE     = '/publish';

    public function init(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void {
        // GET /hubr/v1/categories — list all categories
        register_rest_route( self::NAMESPACE, '/categories', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_categories' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        register_rest_route( self::NAMESPACE, self::ROUTE, [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle' ],
            'permission_callback' => [ $this, 'check_permission' ],
            'args'                => [
                'title'            => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'content'          => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'wp_kses_post',
                ],
                'excerpt'          => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'default'           => '',
                ],
                'status'           => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_key',
                    'default'           => 'publish',
                    'enum'              => [ 'publish', 'draft', 'pending' ],
                ],
                'category_id'      => [
                    'required'          => false,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'default'           => 0,
                ],
                'publish_date'     => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                ],
                'tags'             => [
                    'required' => false,
                    'type'     => 'string',
                    'default'  => '',
                ],
                'meta_title'       => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                ],
                'meta_desc'        => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'default'           => '',
                ],
                'image_base64'     => [
                    'required' => false,
                    'type'     => 'string',
                    'default'  => '',
                ],
                'image_filename'   => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_file_name',
                    'default'           => 'image.jpg',
                ],
                'video_base64'     => [
                    'required' => false,
                    'type'     => 'string',
                    'default'  => '',
                ],
                'video_filename'   => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_file_name',
                    'default'           => 'video.mp4',
                ],
                'post_format'      => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_key',
                    'default'           => '',
                    'enum'              => [ '', 'standard', 'video', 'image', 'gallery', 'audio', 'link', 'quote', 'status', 'aside', 'chat' ],
                ],
                'images'           => [
                    'required' => false,
                    'type'     => 'string', // JSON array of {base64, filename}
                    'default'  => '',
                ],
                'publish_date'     => [
                    'required' => false,
                    'type'     => 'string',
                    'default'  => '',
                ],
                'days_delay'       => [
                    'required'          => false,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'default'           => 0,
                    'description'       => 'Publish N days from now. Overrides publish_date if > 0.',
                ],
            ],
        ] );
    }

    public function handle_categories(): WP_REST_Response {
        $terms = get_terms( [
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        $result = [];
        foreach ( $terms as $term ) {
            $result[] = [
                'id'     => $term->term_id,
                'name'   => $term->name,
                'slug'   => $term->slug,
                'parent' => $term->parent,
                'count'  => $term->count,
            ];
        }

        return new WP_REST_Response( [ 'categories' => $result ] );
    }

    public function check_permission( WP_REST_Request $request ): bool|WP_Error {
        // Apache often strips Authorization header — check multiple sources
        $header = $request->get_header( 'Authorization' )
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if ( ! $header || ! str_starts_with( $header, 'Bearer ' ) ) {
            return new WP_Error( 'rest_forbidden', 'API key required.', [ 'status' => 401 ] );
        }

        $key = substr( $header, 7 );

        if ( ! defined( 'HUBR_API_KEY' ) || ! hash_equals( HUBR_API_KEY, $key ) ) {
            return new WP_Error( 'rest_forbidden', 'Invalid API key.', [ 'status' => 403 ] );
        }

        return true;
    }

    private function get_random_author_id(): int {
        $roles = [ 'subscriber', 'contributor', 'author', 'editor' ];
        foreach ( $roles as $role ) {
            $users = get_users( [
                'fields'  => 'ID',
                'number'  => 10,
                'orderby' => 'ID',
                'order'   => 'ASC',
                'role'    => $role,
            ] );
            if ( ! empty( $users ) ) {
                return (int) $users[ array_rand( $users ) ];
            }
        }
        return defined( 'HUBR_API_AUTHOR_ID' ) ? (int) HUBR_API_AUTHOR_ID : 1;
    }

    public function handle( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $author_id = $this->get_random_author_id();

        $content = $request->get_param( 'content' );

        // Upload images — first becomes featured, all saved to gallery meta
        $attach_id  = 0;
        $gallery_ids = [];

        // Multi-image array (new format)
        $images_json = $request->get_param( 'images' );
        if ( $images_json ) {
            $images_list = json_decode( $images_json, true ) ?: [];
            foreach ( $images_list as $img ) {
                $result = $this->upload_image( $img['base64'], $img['filename'] );
                if ( ! is_wp_error( $result ) ) {
                    $gallery_ids[] = $result;
                    if ( ! $attach_id ) $attach_id = $result;
                }
            }
        }

        // Legacy single image fallback
        if ( ! $attach_id ) {
            $image_base64 = $request->get_param( 'image_base64' );
            if ( $image_base64 ) {
                $result = $this->upload_image( $image_base64, $request->get_param( 'image_filename' ) );
                if ( ! is_wp_error( $result ) ) {
                    $attach_id   = $result;
                    $gallery_ids = [ $result ];
                }
            }
        }

        // Upload video and prepend carousel shortcode to content
        $video_base64 = $request->get_param( 'video_base64' );
        if ( $video_base64 ) {
            $video_id = $this->upload_image( $video_base64, $request->get_param( 'video_filename' ) );
            if ( ! is_wp_error( $video_id ) ) {
                $video_url = wp_get_attachment_url( $video_id );
                $ids_attr  = ! empty( $gallery_ids ) ? ' ids="' . implode( ',', $gallery_ids ) . '"' : '';
                $content   = "[hubr_gallery video=\"{$video_url}\"{$ids_attr}]\n\n" . $content;
                // also keep wp native video shortcode for fallback — removed, using hubr_gallery only
            }
        }

        $days_delay   = (int) $request->get_param( 'days_delay' );
        $publish_date = trim( $request->get_param( 'publish_date' ) );

        $status    = $request->get_param( 'status' );
        $timestamp = false;

        if ( $days_delay > 0 ) {
            $timestamp = strtotime( "+{$days_delay} days" );
            $status    = 'future';
        } elseif ( $publish_date ) {
            // datetime-local comes without timezone — treat as site local time
            $tz        = wp_timezone();
            $dt        = \DateTime::createFromFormat( 'Y-m-d\TH:i', $publish_date, $tz )
                      ?: \DateTime::createFromFormat( 'Y-m-d H:i:s', $publish_date, $tz )
                      ?: \DateTime::createFromFormat( 'Y-m-d H:i', $publish_date, $tz );
            if ( $dt ) {
                $timestamp = $dt->getTimestamp();
                $status    = 'future';
            }
        }

        $post_data = [
            'post_title'     => $request->get_param( 'title' ),
            'post_content'   => $content,
            'post_excerpt'   => $request->get_param( 'excerpt' ),
            'post_status'    => $status,
            'post_author'    => $author_id,
            'comment_status' => 'open',
            'post_type'      => 'post',
        ];

        if ( $timestamp !== false ) {
            $post_data['post_date']     = wp_date( 'Y-m-d H:i:s', $timestamp ); // WP local timezone
            $post_data['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $timestamp );  // UTC
            // WP requires future status only for dates ahead of now
            if ( $timestamp <= time() ) {
                $post_data['post_status'] = 'publish';
            }
        }

        $category_id = $request->get_param( 'category_id' );
        if ( $category_id ) {
            $post_data['post_category'] = [ $category_id ];
        }

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Force author — bypasses WP capability check for subscriber role
        global $wpdb;
        $wpdb->update( $wpdb->posts, [ 'post_author' => $author_id ], [ 'ID' => $post_id ] );
        clean_post_cache( $post_id );

        if ( $attach_id ) {
            set_post_thumbnail( $post_id, $attach_id );
        }

        if ( count( $gallery_ids ) > 1 ) {
            update_post_meta( $post_id, '_hubr_gallery', $gallery_ids );
        }

        $post_format = $request->get_param( 'post_format' );
        if ( $post_format ) {
            update_post_meta( $post_id, 'format', $post_format );
            set_post_format( $post_id, $post_format );
        }

        $tags = trim( $request->get_param( 'tags' ) );
        if ( $tags ) {
            wp_set_post_tags( $post_id, $tags );
        }

        $meta_title = $request->get_param( 'meta_title' );
        if ( $meta_title ) {
            update_post_meta( $post_id, '_hubr_seo_title', $meta_title );
        }

        $meta_desc = $request->get_param( 'meta_desc' );
        if ( $meta_desc ) {
            update_post_meta( $post_id, '_hubr_seo_description', $meta_desc );
        }

        return new WP_REST_Response( [
            'success'   => true,
            'post_id'   => $post_id,
            'author_id' => (int) get_post_field( 'post_author', $post_id ),
            'post_url'  => get_the_permalink( $post_id ),
            'edit_url'  => get_edit_post_link( $post_id, 'raw' ),
        ], 201 );
    }

    private function upload_image( string $base64, string $filename ): int|WP_Error {
        $data = base64_decode( $base64, true );
        if ( $data === false ) {
            return new WP_Error( 'invalid_image', 'Invalid base64 data.' );
        }

        $upload = wp_upload_bits( $filename, null, $data );
        if ( ! empty( $upload['error'] ) ) {
            return new WP_Error( 'upload_error', $upload['error'] );
        }

        $filetype  = wp_check_filetype( $filename );
        $attach_id = wp_insert_attachment( [
            'post_mime_type' => $filetype['type'],
            'post_title'     => pathinfo( $filename, PATHINFO_FILENAME ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ], $upload['file'] );

        if ( is_wp_error( $attach_id ) ) {
            return $attach_id;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        wp_update_attachment_metadata(
            $attach_id,
            wp_generate_attachment_metadata( $attach_id, $upload['file'] )
        );

        return $attach_id;
    }
}
