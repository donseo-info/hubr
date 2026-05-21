<?php

namespace WPShop\WPCommunity\Metaboxes;

use DateTime;
use DateTimeZone;
use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\OrderStatus;
use function WPShop\WPCommunity\get_user_name;
use function WPShop\WPCommunity\theme_container;

class MetaboxOrder {

    const POST_TYPES = [ 'order' ];

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta_box' ], 10, 2 );
    }

    public function add_meta_boxes() {

        add_meta_box(
            'meta_box_order',
            __( 'Order info', 'wpcommunity' ),
            [ $this, 'render_meta_box' ],
            self::POST_TYPES,
            'advanced',
            'default'
        );

    }

    /**
     * @param \WP_Post $post
     *
     * @return void
     */
    public function render_meta_box( $post ) {

        $orders       = theme_container()->get( Orders::class );
        $order_status = theme_container()->get( OrderStatus::class );
        $order        = $orders->get_order( $post->ID );

        // Retrieve an existing value from the database.
        if ( is_wp_error( $order ) ) {
            echo $order->get_error_message();

            return;
        }

//		$order_key = get_post_meta( $post->ID, Orders::POST_META_KEY, true );
//		$type = get_post_meta( $post->ID, Orders::POST_META_TYPE, true );
//		$status = get_post_meta( $post->ID, Orders::POST_META_STATUS, true );
//		$user_id = get_post_meta( $post->ID, Orders::POST_META_USER_ID, true );
//
//		$price = get_post_meta( $post->ID, Orders::POST_META_PRICE, true );
//		$price_total = get_post_meta( $post->ID, Orders::POST_META_PRICE_TOTAL, true );
//
//		$subscription_id = get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_ID, true );
//		$subscription_price = get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_PRICE, true );
//		$qty = get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_QTY, true );


        // Form fields.
        echo '<table class="form-table">';

        echo '	<tr>';
        echo '		<th><label for="subscription">' . __( 'General', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';
        echo '			<strong>#' . $order->order_id . ' ' . $order->title . '</strong>';
        echo '			<a href="' . $orders->get_order_link( $post->ID ) . '">[ ↗ ]</a><br>';
        echo '			' . $order->status_text_colored . ' <sup>' . $order->status . '</sup><br><br>';
        echo '			type: ' . $order->type . '<br>';
        echo '			user: <a href="' . get_edit_user_link( $order->user_id ) . '">' . $order->user_email . '</a><br>';
        echo '		</td>';
        echo '	</tr>';

        echo '	<tr>';
        echo '		<th><label for="subscription">' . __( 'Subscription', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';
        echo '			subscription_id: ' . $order->get_subscription()->id . '<br>';
        echo '			' . $order->subscription_price . '&times;' . $order->qty;
        echo '		</td>';
        echo '	</tr>';

        echo '	<tr>';
        echo '		<th><label for="description">' . __( 'Price', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';
        echo '			' . $order->price . ' ' . $order->currency_beauty . '<br>';
        echo '			Price total: ' . $order->price_total . '<br>';
        echo '			Income: ' . $order->income . ' ' . $order->income_currency . '<br>';
        if ( $order->refund ) {
            echo '			Refund: ' . $order->refund . ' ' . $order->currency_beauty . '<br>';
        }
        echo '		</td>';
        echo '	</tr>';

        echo '	<tr>';
        echo '		<th><label for="description">' . __( 'Payment provider', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';
        echo '<div class="order-refund js-wpcommunity-order-refund-container">';
        echo '    <div>' . ( $order->provider ?: __( '<i>(null)</i>', 'wpcommunity' ) ) . ' ' . ( $order->payment_type ?: __( '<i>(null)</i>', 'wpcommunity' ) ) . '</div>';
        $refund_disabled = disabled(
            false,
            $orders->can_refund_by_provider( $order->order_id ) && $order_status->can_change_status( $order->status, OrderStatus::REFUNDED ),
            false
        );
        echo '    <input type="number" class="js-wpcommunity-order-refund-amount" value="' . $orders->get_available_refund_amount( $order->order_id ) . '" max="' . $orders->get_available_refund_amount( $order->order_id ) . '"' . $refund_disabled . '>';
        echo '    <button class="button js-wpcommunity-order-refund" data-order_id="' . $order->order_id . '"' . $refund_disabled . '>' . __( 'Make a Refund', 'wpcommunity' ) . '</button>';
        echo '</div><!-- .order-refund -->';
        echo '<p>Payment Id: <code>' . $order->payment_id . '</code></p>';
        echo '		</td>';
        echo '	</tr>';

        echo '	<tr>';
        echo '		<th><label for="description">' . __( 'Order Status', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';

        echo '<div class="order-status js-wpcommunity-order-status-container">';
        echo '    <select class="js-wpcommunity-order-status">';
        echo '        <option value="">' . __( '-- select status --', 'wpcommunity' ) . '</option>';
        foreach ( [ OrderStatus::COMPLETED, OrderStatus::CANCELLED, OrderStatus::REFUNDED ] as $_status ) {
            printf( '<option value="%d"%s>%s</option>',
                $_status,
                disabled( true, ! $order_status->can_change_status( $order->status, $_status ), false ),
                $order_status->get_status_description( $_status )
            );
        }
        echo '    </select>';
        echo '    <button class="button js-wpcommunity-order-change-status" data-order_id="' . $order->order_id . '">' . __( 'Change Status', 'wpcommunity' ) . '</button>';
        echo '</div>';
        echo '<p class="description">' . __( 'There will be no automatic refund and unsubscribe when the status changes. This all needs to be handled manually.', 'wpcommunity' ) . '</p>';

        echo '		</td>';
        echo '	</tr>';

        // повторные платежи


        echo '  <tr>';
        echo '    <th>' . __( 'Recurring Payment', 'wpcommunity' ) . '</th>';
        echo '    <td>';

        echo '<div>';
        if ( $recurring_date = get_post_meta( $post->ID, Orders::POST_META_RECURRING_DATE, true ) ) {
            echo __( 'Next Date', 'wpcommunity' ) . ': ';
            echo wp_date( 'j F Y H:i:s T', $recurring_date, new DateTimeZone( 'UTC' ) );
        } else {
            echo __( '<i>(null)</i>', 'wpcommunity' );
        }
        echo '</div>';

        if ( $recurring_children = (array) ( get_post_meta( $post->ID, Orders::POST_META_RECURRING_CHILDREN, true ) ?: [] ) ) {
            // данные родительского заказа

            echo '<div>';
            $links = [];
            foreach ( $recurring_children as $child_id ) {
                $links[] = sprintf(
                    '<a href="%s" target="_blank">%s [%d]</a>',
                    get_edit_post_link( $child_id ),
                    __( 'order', 'wpcommunity' ),
                    $child_id
                );
            }
            echo implode( '<br>', $links );
            echo '<div>';

            echo '<div>';
            echo __( 'Recurring History', 'wpcommunity' );
            echo '<br>';

            foreach ( (array) ( get_post_meta( $post->ID, Orders::POST_META_RECURRING_HISTORY, true ) ?: [] ) as $history_item ) {
                if ( ! $history_item ) {
                    continue;
                }

                $history_item = wp_parse_args( $history_item, [
                    'created_at_gmt' => '',
                    'user_id'        => '',
                    'text'           => '',
                ] );

                $history_item_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $history_item['created_at_gmt'], new DateTimeZone( 'UTC' ) );
                if ( $history_item_date ) {
                    $history_item_date->setTimezone( wp_timezone() );
                    $history_item_date = date_i18n(
                        get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                        $history_item_date->getTimestamp() + $history_item_date->getOffset()
                    );
                }


                $user = get_userdata( $history_item['user_id'] );

                printf(
                    '<strong>%s</strong><br>%s: %s</br>',
                    $user ? '<a href="' . get_edit_user_link( $user->ID ) . '" target="_blank">' . get_user_name( $user->ID ) . '</a>' : '(no user)',
                    $history_item_date,
                    esc_html( $history_item['text'] )
                );
            }
            echo '</div>';

        } else if ( $recurring_parent = get_post_meta( $post->ID, Orders::POST_META_RECURRING_PARENT, true ) ) {
            // данные дочернего заказа
            printf(
                '<a href="%s" target="_blank">%s</a>',
                get_edit_post_link( $recurring_parent ),
                __( 'Parent Order', 'wpcommunity' )
            );
        }

        echo '    </td>';
        echo '  </tr>';

        echo '	<tr>';
        echo '		<th><label for="description">' . __( 'Status Hostory', 'wpcommunity' ) . '</label></th>';
        echo '		<td>';
        echo '          <div class="order-status-history">';
        foreach ( $order->status_history as $history ) {
            printf( '<div><strong>%s</strong><br>%s <span>%s</span> → <span>%s</span></div>',
                $history->user_id ? '<a href="' . get_edit_user_link( $history->user_id ) . '">' . $history->user_name . '</a>' : $history->user_name,
                $history->get_created_at(),
                $order_status->get_status_description( $history->old_status ) ?: __( '<i>(null)</i>', 'wpcommunity' ),
                $order_status->get_status_description( $history->new_status )
            );
        }
        echo '          </div>';
        echo '		</td>';
        echo '	</tr>';

        echo '</table>';

    }


    public function save_meta_box( $post_id, $post ) {

        // проверяем, может ли текущий юзер редактировать пост
        $post_type = get_post_type_object( $post->post_type );
        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return $post_id;
        }

        // ничего не делаем для автосохранений
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // проверяем тип записи
        if ( ! in_array( $post->post_type, self::POST_TYPES ) ) {
            return $post_id;
        }


        if ( isset( $_POST['description'] ) ) {
            update_post_meta( $post_id, 'description', sanitize_text_field( $_POST['description'] ) );
        } else {
            delete_post_meta( $post_id, 'description' );
        }

//		$for_newbie = isset( $_POST[ 'for_newbie' ] ) ? 'checked'  : '';
//		$days = isset( $_POST[ 'days' ] ) ? (int) $_POST[ 'days' ] : 0;
//		$expired = isset( $_POST[ 'expired' ] ) ? sanitize_text_field( $_POST[ 'expired' ] ) : '';
//		$limit = isset( $_POST[ 'limit' ] ) ? (int) $_POST[ 'limit' ] : 0;
//		$description = isset( $_POST[ 'description' ] ) ? sanitize_text_field( $_POST[ 'description' ] ) : '';
//
//		if ( empty( $expired ) ) $expired = date( 'Y-m-d', strtotime( '+1 month' ) );

//		$chimp_new_send_at = isset( $_POST[ 'chimp_send_at' ] ) ? sanitize_text_field( $_POST[ 'chimp_send_at' ] ) : '';
//		$chimp_new_mailing_list = isset( $_POST[ 'chimp_mailing_list' ] ) ? $_POST[ 'chimp_mailing_list' ] : '';
//		$chimp_new_from_name = isset( $_POST[ 'chimp_from_name' ] ) ? sanitize_text_field( $_POST[ 'chimp_from_name' ] ) : '';
//		$chimp_new_reply_to = isset( $_POST[ 'chimp_reply_to' ] ) ? sanitize_email( $_POST[ 'chimp_reply_to' ] ) : '';
//		$chimp_new_subject = isset( $_POST[ 'chimp_subject' ] ) ? sanitize_text_field( $_POST[ 'chimp_subject' ] ) : '';

        // Update the meta field in the database.
//		update_post_meta( $post_id, 'description', $description );
//		update_post_meta( $post_id, 'days', $days );
//		update_post_meta( $post_id, 'expired', $expired );
//		update_post_meta( $post_id, 'limit', $limit );
//		update_post_meta( $post_id, 'description', $description );

//		update_post_meta( $post_id, 'chimp_send_at', $chimp_new_send_at );
//		update_post_meta( $post_id, 'chimp_mailing_list', $chimp_new_mailing_list );
//		update_post_meta( $post_id, 'chimp_from_name', $chimp_new_from_name );
//		update_post_meta( $post_id, 'chimp_reply_to', $chimp_new_reply_to );
//		update_post_meta( $post_id, 'chimp_subject', $chimp_new_subject );

    }

//	public function render_fields() {
//		$this->field_number( 'days', 'Добавляет дней', '', '' );
//		$this->field_text( 'expired', 'Истекает', '', date('Y-m-d', strtotime('next month')) );
////		$this->field_select( 'access', 'Доступ', [
////			'default' => 'По умолчанию',
////			'public' => 'Public',
////			'private' => 'Private',
////		] );
//	}

}
