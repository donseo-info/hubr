<?php

namespace WPShop\WPCommunity\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class CommentsEndpoint {

    const NAMESPACE = 'hubr/v1';

    public function init(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void {
        // GET /hubr/v1/posts — list posts with comment counts
        register_rest_route( self::NAMESPACE, '/posts', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_list' ],
            'permission_callback' => [ $this, 'check_permission' ],
            'args'                => [
                'page'             => [
                    'required' => false,
                    'type'     => 'integer',
                    'default'  => 1,
                    'minimum'  => 1,
                ],
                'per_page'         => [
                    'required' => false,
                    'type'     => 'integer',
                    'default'  => 20,
                    'minimum'  => 1,
                    'maximum'  => 100,
                ],
                'no_comments_only' => [
                    'required' => false,
                    'type'     => 'boolean',
                    'default'  => false,
                ],
            ],
        ] );

        // GET /hubr/v1/posts/{id} — single post with comments
        register_rest_route( self::NAMESPACE, '/posts/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_get' ],
            'permission_callback' => [ $this, 'check_permission' ],
            'args'                => [
                'id' => [
                    'required' => true,
                    'type'     => 'integer',
                    'minimum'  => 1,
                ],
            ],
        ] );

        // GET /hubr/v1/personas — list persona users
        register_rest_route( self::NAMESPACE, '/personas', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_personas' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        // POST /hubr/v1/posts/{id}/comment — add comment or reply
        register_rest_route( self::NAMESPACE, '/posts/(?P<id>\d+)/comment', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_comment' ],
            'permission_callback' => [ $this, 'check_permission' ],
            'args'                => [
                'id'        => [
                    'required' => true,
                    'type'     => 'integer',
                    'minimum'  => 1,
                ],
                'content'   => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'wp_kses_post',
                ],
                'parent_id' => [
                    'required' => false,
                    'type'     => 'integer',
                    'default'  => 0,
                ],
                'author_name' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => 'Bot',
                ],
                'author_email' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_email',
                    'default'           => '',
                ],
                'author_user_id' => [
                    'required' => false,
                    'type'     => 'integer',
                    'default'  => 0,
                ],
            ],
        ] );
    }

    public function check_permission( WP_REST_Request $request ): bool|WP_Error {
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

    public function handle_personas(): WP_REST_Response {
        $users = get_users( [
            'meta_key'   => 'is_persona',
            'meta_value' => '1',
        ] );

        $result = [];
        foreach ( $users as $user ) {
            $local_avatar = get_user_meta( $user->ID, '_local_avatar', true );
            $avatar       = is_array( $local_avatar ) ? ( $local_avatar['thumb'] ?? '' ) : '';
            $result[]     = [
                'id'           => $user->ID,
                'login'        => $user->user_login,
                'display_name' => $user->display_name,
                'email'        => $user->user_email,
                'avatar'       => $avatar,
                'gender'       => get_user_meta( $user->ID, 'gender', true ) ?: 'm',
            ];
        }

        return new WP_REST_Response( [ 'personas' => $result ] );
    }

    public function handle_list( WP_REST_Request $request ): WP_REST_Response {
        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $no_comments_only = $request->get_param( 'no_comments_only' );

        $query_args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ( $no_comments_only ) {
            $query_args['comment_count'] = [ 'value' => 0, 'compare' => '=' ];
        }

        $query = new \WP_Query( $query_args );

        $posts = [];
        foreach ( $query->posts as $post ) {
            $posts[] = [
                'id'            => $post->ID,
                'title'         => $post->post_title,
                'excerpt'       => get_the_excerpt( $post ),
                'url'           => get_permalink( $post->ID ),
                'date'          => $post->post_date,
                'comment_count' => (int) $post->comment_count,
            ];
        }

        return new WP_REST_Response( [
            'posts'       => $posts,
            'total'       => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'page'        => $page,
        ] );
    }

    public function handle_get( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $post_id = $request->get_param( 'id' );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_status !== 'publish' || $post->post_type !== 'post' ) {
            return new WP_Error( 'not_found', 'Post not found.', [ 'status' => 404 ] );
        }

        $raw_comments = get_comments( [
            'post_id' => $post_id,
            'status'  => 'approve',
            'orderby' => 'comment_date',
            'order'   => 'ASC',
        ] );

        $comments = array_map( function ( $c ) {
            return [
                'id'          => (int) $c->comment_ID,
                'parent_id'   => (int) $c->comment_parent,
                'author'      => $c->comment_author,
                'author_id'   => (int) $c->user_id,
                'content'     => $c->comment_content,
                'date'        => $c->comment_date,
            ];
        }, $raw_comments );

        return new WP_REST_Response( [
            'id'            => $post->ID,
            'title'         => $post->post_title,
            'content'       => $post->post_content,
            'excerpt'       => get_the_excerpt( $post ),
            'url'           => get_permalink( $post->ID ),
            'date'          => $post->post_date,
            'comment_count' => (int) $post->comment_count,
            'comments'      => $comments,
        ] );
    }

    public function handle_comment( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $post_id = $request->get_param( 'id' );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_status !== 'publish' || $post->post_type !== 'post' ) {
            return new WP_Error( 'not_found', 'Post not found.', [ 'status' => 404 ] );
        }

        if ( $post->comment_status !== 'open' ) {
            return new WP_Error( 'comments_closed', 'Comments are closed for this post.', [ 'status' => 403 ] );
        }

        $parent_id = (int) $request->get_param( 'parent_id' );

        if ( $parent_id ) {
            $parent = get_comment( $parent_id );
            if ( ! $parent || (int) $parent->comment_post_ID !== $post_id ) {
                return new WP_Error( 'invalid_parent', 'Parent comment not found on this post.', [ 'status' => 400 ] );
            }
        }

        $author_user_id = (int) $request->get_param( 'author_user_id' );
        $author_name    = $request->get_param( 'author_name' );
        $author_email   = $request->get_param( 'author_email' );

        if ( $author_user_id ) {
            $user = get_user_by( 'ID', $author_user_id );
            if ( $user ) {
                $author_name  = $author_name ?: $user->display_name;
                $author_email = $author_email ?: $user->user_email;
            }
        }

        $comment_id = wp_insert_comment( [
            'comment_post_ID'      => $post_id,
            'comment_content'      => $request->get_param( 'content' ),
            'comment_parent'       => $parent_id,
            'comment_author'       => $author_name,
            'comment_author_email' => $author_email,
            'user_id'              => $author_user_id,
            'comment_approved'     => 1,
            'comment_date'         => current_time( 'mysql' ),
            'comment_date_gmt'     => current_time( 'mysql', true ),
        ] );

        if ( ! $comment_id || is_wp_error( $comment_id ) ) {
            return new WP_Error( 'insert_failed', 'Failed to insert comment.', [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [
            'success'    => true,
            'comment_id' => $comment_id,
        ], 201 );
    }
}
