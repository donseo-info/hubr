<?php

namespace WPShop\WPCommunity\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class BoostScoreEndpoint {

    const NAMESPACE = 'hubr/v1';
    const ROUTE     = '/boost-score';

    public function init(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void {
        register_rest_route( self::NAMESPACE, self::ROUTE, [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle' ],
            'permission_callback' => [ $this, 'check_permission' ],
            'args'                => [
                'post_id' => [
                    'required'          => false,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'default'           => 0,
                ],
                'random' => [
                    'required' => false,
                    'type'     => 'boolean',
                    'default'  => false,
                ],
                'amount' => [
                    'required'          => false,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'default'           => 0,
                    'description'       => 'Exact boost amount. If 0 — random 1..5.',
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

    public function handle( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $post_id = (int) $request->get_param( 'post_id' );
        $random  = (bool) $request->get_param( 'random' );
        $amount  = (int) $request->get_param( 'amount' );

        // Pick random published post if needed
        if ( $random && ! $post_id ) {
            $ids = get_posts( [
                'post_status'    => 'publish',
                'post_type'      => 'post',
                'fields'         => 'ids',
                'posts_per_page' => 50,
                'orderby'        => 'rand',
            ] );
            if ( empty( $ids ) ) {
                return new WP_Error( 'no_posts', 'No published posts found.', [ 'status' => 404 ] );
            }
            $post_id = (int) $ids[ array_rand( $ids ) ];
        }

        if ( ! $post_id ) {
            return new WP_Error( 'missing_post_id', 'post_id required or set random=true.', [ 'status' => 400 ] );
        }

        if ( ! get_post( $post_id ) ) {
            return new WP_Error( 'not_found', 'Post not found.', [ 'status' => 404 ] );
        }

        // Determine boost amount
        if ( $amount <= 0 ) {
            $amount = rand( 1, 5 );
        }
        $amount = min( $amount, 50 ); // cap

        $likes    = (int) get_post_meta( $post_id, 'vote_likes',    true );
        $dislikes = (int) get_post_meta( $post_id, 'vote_dislikes', true );

        $likes += $amount;
        $activity = $likes + $dislikes;
        $score    = $likes - $dislikes;

        update_post_meta( $post_id, 'vote_likes',    $likes );
        update_post_meta( $post_id, 'vote_activity', $activity );
        update_post_meta( $post_id, 'vote_score',    $score );

        return new WP_REST_Response( [
            'success'   => true,
            'post_id'   => $post_id,
            'post_url'  => get_permalink( $post_id ),
            'boosted'   => $amount,
            'new_score' => $score,
            'new_likes' => $likes,
        ], 200 );
    }
}
