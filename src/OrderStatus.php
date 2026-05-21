<?php

namespace WPShop\WPCommunity;

use WP_Error;

class OrderStatus {

    const PENDING   = 0;
    const COMPLETED = 1;
    const FAILED    = 2;
    const CANCELLED = 3;
    const REFUNDED  = 4;

    /**
     * @param int|null $old_status
     * @param int      $new_status
     *
     * @return bool
     */
    public function can_change_status( $old_status, $new_status ) {
        // новый заказ (без старого статуса) можно поменять только на PENDING и FAILED
        if ( null === $old_status ) {
            return in_array( $new_status, [ self::PENDING, self::FAILED ] );
        }

        // если заказ в ожидании (PENDING), то его можно перевести только в завершенный (COMPLETED), неудачный (FAILED)
        // и отменить (CANCELLED)
        if ( self::PENDING == $old_status ) {
            return in_array( $new_status, [ self::COMPLETED, self::FAILED, self::CANCELLED ] );
        }

        // завершенный заказ можно перевести в возврат (REFUNDED)
        // и завершенный (COMPLETED) - это может быть в случае оплаты по подписке по тому же заказу
        if ( self::COMPLETED == $old_status ) {
            return in_array( $new_status, [ self::REFUNDED, self::COMPLETED ] );
        }

        if ( self::CANCELLED == $old_status ) {

        }

        // при возврате можно поменять только на (REFUNDED),
        // это может быть при частичном списании и дополнительном списании
        if ( self::REFUNDED == $old_status ) {
            return in_array( $new_status, [ self::REFUNDED ] );
        }

        if ( self::FAILED == $old_status ) {

        }

        return false;
    }

    /**
     * @param int      $order_id
     * @param int      $new_status
     * @param int|null $user_id
     *
     * @return bool|WP_Error
     */
    public function change_status( $order_id, $new_status, $user_id = null ) {
        $old_status = get_post_meta( $order_id, Orders::POST_META_STATUS, true );
        if ( '' === $old_status ) {
            $old_status = null;
        }
        if ( ! $this->can_change_status( $old_status, $new_status ) ) {
            return new WP_Error(
                'status_status',
                sprintf( __( 'Unable to change order status from "%s" to "%s"' ),
                    self::get_status_description( $old_status ),
                    self::get_status_description( $new_status )
                )
            );
        }

        return $this->_change_status( $order_id, $old_status, $new_status, $user_id );
    }

    /**
     * @param int      $order_id
     * @param int      $new_status
     * @param int|null $user_id
     *
     * @return bool|WP_Error
     */
    public function force_change_status( $order_id, $new_status, $user_id = null ) {
        $old_status = get_post_meta( $order_id, Orders::POST_META_STATUS, true );
        if ( '' === $old_status ) {
            $old_status = null;
        }

        return $this->_change_status( $order_id, $old_status, $new_status, $user_id );
    }

    /**
     * @param int      $order_id
     * @param int      $old_status
     * @param int      $new_status
     * @param int|null $user_id
     *
     * @return bool|WP_Error
     */
    protected function _change_status( $order_id, $old_status, $new_status, $user_id = null ) {
        if ( null === $user_id ) {
            $user = wp_get_current_user();
            if ( ! $user ) {
                return new WP_Error( 'order_status', __( 'Not logged in users cannot change order statuses', 'wpcommunity' ) );
            }
            $user_id   = $user->ID;
            $user_name = get_user_name( $user );
        } elseif ( is_string( $user_id ) ) {
            $user_name = $user_id;
            $user_id   = 0;
        } elseif ( is_numeric( $user_id ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( ! $user ) {
                return new WP_Error( 'order_status', __( 'Only existing users can change order statuses', 'wpcommunity' ) );
            }
            $user_id   = $user->ID;
            $user_name = get_user_name( $user );
        }

        $created_at_gmt = current_time( 'mysql', true );

        $history = get_post_meta( $order_id, Orders::POST_META_STATUS_HISTORY, true );
        if ( ! $history ) {
            $history = [];
        }
        $history[] = $history_item = compact( 'created_at_gmt', 'old_status', 'new_status', 'user_id', 'user_name' );

        update_post_meta( $order_id, Orders::POST_META_STATUS, $new_status );
        update_post_meta( $order_id, Orders::POST_META_STATUS_HISTORY, $history );

        do_action( 'wpcommunity/order_status/change_status', $history_item );

        return true;
    }


    /**
     * @param int $status
     *
     * @return string|null
     */
    public function get_status_description( $status = null ) {
        return
            [
                self::PENDING   => _x( 'Pending', 'order status', 'wpcommunity' ),
                self::COMPLETED => _x( 'Completed', 'order status', 'wpcommunity' ),
                self::FAILED    => _x( 'Failed', 'order status', 'wpcommunity' ),
                self::CANCELLED => _x( 'Canceled', 'order status', 'wpcommunity' ),
                self::REFUNDED  => _x( 'Refunded', 'order status', 'wpcommunity' ),
            ][ $status ] ?? null;
    }
}
