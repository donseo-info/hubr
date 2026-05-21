<?php

namespace WPShop\WPCommunity;

use WP_Error;
use WPShop\WPCommunity\Data\OrderData;
use WPShop\WPCommunity\Data\StatusHistory;
use WPShop\WPCommunity\Data\SubscriptionPlan;
use WPShop\WPCommunity\PaymentProviders\RecurringPayments;
use WPShop\WPCommunity\PaymentProviders\YooKassa;

class Orders {

    const POST_TYPE = 'order';

    const ORDER_TYPE_SUBSCRIPTION = 'subscription';

    const POST_META_STATUS         = '_status';
    const POST_META_STATUS_HISTORY = '_status_history';
    const POST_META_TYPE           = '_type';
    const POST_META_KEY            = '_key';
    const POST_META_USER_ID        = '_user_id';
    const POST_META_PRICE          = '_price'; // итоговая цена для оплаты
    const POST_META_REFUND         = '_refund'; // итоговая сумма возврата
    const POST_META_REFUND_HISTORY = '_refund_history'; // итоговая возвратов
    const POST_META_PRICE_TOTAL    = '_price_total'; // полная цена с учетом фантиков, например, из 1000 руб клиент заплатил 700 руб с баланса, учитываем их тут
    const POST_META_CURRENCY       = '_currency';

    /**
     * Помечаем заказ на будущее, чтобы при успешной оплате добавить дату следующего списания.
     * Если пользователь нажал галочку
     */
    const POST_META_IS_RECURRING = '_is_recurring';
    /**
     * Запланированная дата следующей оплаты
     */
    const POST_META_RECURRING_DATE     = '_recurring_date_gmt';
    const POST_META_RECURRING_HISTORY  = '_recurring_history';
    const POST_META_RECURRING_CHILDREN = '_recurring_children';
    const POST_META_RECURRING_ERRORS   = '_recurring_errors_count';
    const POST_META_RECURRING_NOTIFIED = '_recurring_notified';

    const POST_META_RECURRING_PARENT = '_recurring_parent';


    const POST_META_INCOME          = '_income'; // цена за вычетом комиссии платежной системы, что пришло
    const POST_META_INCOME_CURRENCY = '_income_currency'; // валюта, в которой пришла оплата

    const POST_META_PROVIDER           = '_provider';
    const POST_META_PAYMENT_TYPE       = '_payment_type'; // способ оплаты, картой и тд
    const POST_META_PAYMENT_REFUNDABLE = '_payment_refundable'; // можно ли сделать возврат

    const POST_META_SUBSCRIPTION_ID    = 'subscription_id';
    const POST_META_SUBSCRIPTION_PRICE = 'subscription_price';
    const POST_META_SUBSCRIPTION_QTY   = 'subscription_qty';
    const POST_META_SUBSCRIPTION_DAYS  = 'subscription_days';

    protected $order_status;

    /**
     * @param OrderStatus $order_status
     */
    public function __construct( OrderStatus $order_status ) {
        $this->order_status = $order_status;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, '_register_post_type' ] );

        new \WPShop\WPCommunity\Metaboxes\MetaboxOrder();

        add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', [ $this, '_admin_columns_add' ], 4 );
        add_filter( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, '_admin_columns_fill' ], 5, 2 );
    }

    /**
     * @param $columns
     *
     * @return mixed
     */
    public function _admin_columns_add( $columns ) {
        unset ( $columns['author'] );
        unset ( $columns['title'] );
        unset ( $columns['date'] );
        $columns['order_title'] = _x( 'Order', 'orders', 'wpcommunity' );
        $columns['price']       = _x( 'Price', 'orders', 'wpcommunity' );
//		$columns['partner'] = 'Партнер';
        $columns['status']       = _x( 'Status', 'orders', 'wpcommunity' );
        $columns['created_date'] = _x( 'Date', 'orders', 'wpcommunity' );

        return $columns;
    }

    /**
     * @param string $column_name
     * @param int    $post_id
     *
     * @return void
     */
    public function _admin_columns_fill( $column_name, $post_id ) {

        $order = $this->get_order( $post_id );

        if ( $column_name == 'order_title' ) {
            echo '<strong>';
            echo '#' . $order->order_id . ' ';
            echo '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">' . $order->title . '</a>';
            echo '</strong><br>';
            echo '<a href="' . admin_url( 'user-edit.php?user_id=' . $order->user_id ) . '">' . $order->user_email . '</a>';
            if ( $parent_id = get_post_meta( $post_id, static::POST_META_RECURRING_PARENT, true ) ) {
                echo '<br>';
                echo '<span class="dashicons dashicons-controls-repeat" title="' . esc_attr__( 'Recurring Order', 'wpcommunity' ) . '"></span> ';
                echo __( 'Parent Order:', 'wpcommunity' );
                echo ' <a href="' . get_edit_post_link( $parent_id ) . '">' . $parent_id . '</a>';
            }

            if ( $recurring_date = get_post_meta( $post_id, Orders::POST_META_RECURRING_DATE, true ) ) {
                $recurring_datetime = new \DateTimeImmutable( '@' . $recurring_date );
                $recurring_datetime = $recurring_datetime->setTimezone( wp_timezone() );
                echo '<br>' . __( 'Recurring payment day', 'wpcommunity' ) . ': ' .
                     wp_date( 'j F Y H:i:s T', $recurring_datetime->getTimestamp() );
            }
        }

        if ( $column_name == 'price' ) {
            echo '<strong>' . $order->price . '&nbsp;' . $order->currency_beauty . '</strong><br>';
            echo $order->provider . ' ' . $order->payment_type;
        }

        if ( $column_name == 'status' ) {
            echo $order->status_text_colored;
        }

        if ( $column_name == 'created_date' ) {
            echo get_the_time( 'd.m.Y ' . _x( 'at', 'orders', 'wpcommunity' ) . ' H:i', $post_id );
        }
    }


    /**
     * @param $order_id
     *
     * @return OrderData|\WP_Error
     */
    public function get_order( $order_id ) {
        $post = get_post( $order_id );

        if ( $post->post_type != self::POST_TYPE ) {
            return new \WP_Error( 'not_order', __( 'Not order', 'wpcommunity' ) );
        }

        $order_key = get_post_meta( $post->ID, Orders::POST_META_KEY, true );
        $type      = get_post_meta( $post->ID, Orders::POST_META_TYPE, true );
        $status    = (int) get_post_meta( $post->ID, Orders::POST_META_STATUS, true );
        $user_id   = get_post_meta( $post->ID, Orders::POST_META_USER_ID, true );

        $price       = (float) get_post_meta( $post->ID, Orders::POST_META_PRICE, true );
        $price_total = (float) get_post_meta( $post->ID, Orders::POST_META_PRICE_TOTAL, true );
        $currency    = get_post_meta( $post->ID, Orders::POST_META_CURRENCY, true );

        $income          = (float) get_post_meta( $post->ID, Orders::POST_META_INCOME, true );
        $income_currency = get_post_meta( $post->ID, Orders::POST_META_INCOME_CURRENCY, true );
        $refund          = (float) get_post_meta( $post->ID, Orders::POST_META_REFUND, true );

        $provider     = get_post_meta( $post->ID, Orders::POST_META_PROVIDER, true );
        $payment_type = get_post_meta( $post->ID, Orders::POST_META_PAYMENT_TYPE, true );

        $payment_id        = '';
        $provider_instance = theme_container()->get( PaymentProviders::class )->get( $provider );
        if ( $provider_instance && $provider_instance instanceof YooKassa ) {
            $payment_id = get_post_meta( $post->ID, YooKassa::POST_META_PAYMENT_ID, true );
        }

        $subscription_id    = get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_ID, true );
        $subscription_price = (float) get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_PRICE, true );
        $qty                = (int) get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_QTY, true );
        $subscription_days  = (int) get_post_meta( $post->ID, Orders::POST_META_SUBSCRIPTION_DAYS, true );

        $user          = get_user_by( 'ID', $user_id );
        $subscriptions = theme_container()->get( PaidSubscriptions::class );

        $subscription_months = ( ! empty( $subscription['months'] ) ) ? $subscription['months'] : 0;

        $status_history = (array) ( get_post_meta( $post->ID, Orders::POST_META_STATUS_HISTORY, true ) ?: [] );
        $status_history = array_map( function ( $row ) {
            return new StatusHistory( $row );
        }, $status_history );

        // todo обработка если такой подписки уже не существует


        return new OrderData( [
            'order_id'   => $post->ID,
            'order_key'  => $order_key,
            'order_link' => $this->get_order_link( $post->ID ),

            'title'               => $this->get_order_title( $post->ID ),
            'type'                => $type,
            'status'              => $status,
            'status_text'         => $this->get_order_status_text( $post->ID ),
            'status_text_colored' => $this->get_order_status_text_colored( $post->ID ),
            'status_history'      => $status_history,

            'user_id'    => $user_id,
            'user_email' => $user->user_email,

            'price'           => round( $price, 2 ),
            'price_total'     => round( $price_total, 2 ),
            'currency'        => $currency,
            'currency_beauty' => get_currency_beauty( $currency ),
            'income'          => round( $income, 2 ),
            'income_currency' => $income_currency,
            'refund'          => round( $refund, 2 ),

            'provider'     => $provider,
            'payment_type' => $payment_type,
            'payment_id'   => $payment_id,
            //            'payment_data' =>

            'subscription_id'     => $subscription_id,
            'subscription_name'   => $subscriptions->get_subscription( $subscription_id )->name,
            'subscription_months' => $subscription_months,
            'subscription_days'   => $subscription_days,
            'subscription_price'  => $subscription_price,
            'qty'                 => $qty,
        ] );
    }

    /**
     * @param int $order_id
     *
     * @return bool
     */
    public function can_refund_by_provider( $order_id ) {
//        if ( get_post_meta( $order_id, self::POST_META_PAYMENT_REFUNDABLE, true ) ) {
        if ( $this->get_available_refund_amount( $order_id ) ) {
            return true;
        }

//        }

        return false;
    }

    /**
     * @param int $order_id
     *
     * @return float|int
     */
    public function get_available_refund_amount( $order_id ) {
        $price    = (float) get_post_meta( $order_id, Orders::POST_META_PRICE, true );
        $refunded = (float) get_post_meta( $order_id, Orders::POST_META_REFUND, true );
        $amount   = $price - $refunded;
        if ( $amount > 1 ) {
            return $amount;
        }

        return 0;
    }

    /**
     * @param int $order_id
     *
     * @return int
     */
    public function get_order_status( $order_id ) {
        return (int) get_post_meta( $order_id, self::POST_META_STATUS, true );
    }

    /**
     * @param int $order_id
     *
     * @return string|null
     */
    public function get_order_status_text( $order_id ) {
        return $this->order_status->get_status_description( $this->get_order_status( $order_id ) );
    }

    /**
     * @param int $order_id
     *
     * @return string
     */
    public function get_order_status_text_colored( $order_id ) {
        $status = $this->get_order_status( $order_id );
        $text   = $this->get_order_status_text( $order_id );
        $color  = 'inherit';

        $colors = [
            0 => '#db8200',
            1 => '#009e00',
            2 => '#f00',
        ];

        if ( isset( $colors[ $status ] ) ) {
            $color = $colors[ $status ];
        }

        return '<span style="color:' . $color . '">' . $text . '</span>';
    }

    /**
     * @param int $order_id
     *
     * @return string
     */
    public function get_order_title( $order_id ) {
        $type = get_post_meta( $order_id, Orders::POST_META_TYPE, true );

        $order_title = '';

        if ( $type == self::ORDER_TYPE_SUBSCRIPTION ) {
            $order_title .= __( 'Subscription', 'wpcommunity' );

            $subscriptions   = new PaidSubscriptions();
            $subscription_id = get_post_meta( $order_id, Orders::POST_META_SUBSCRIPTION_ID, true );
            $qty             = get_post_meta( $order_id, Orders::POST_META_SUBSCRIPTION_QTY, true );

            $beauty_months = $subscriptions->get_subscription_name_with_qty( $subscription_id, $qty );
            $order_title   .= ' ' . $beauty_months;
        }

        return $order_title;
    }

    /**
     * @param int $order_id
     *
     * @return string
     */
    public function get_order_link( $order_id ) {

        $page_order = (int) get_setting( 'page.order' );
        if ( empty( $page_order ) ) {
            return '#not_defined';
        }

        $order_id_hash = base64_encode( $order_id );
        $order_key     = get_post_meta( $order_id, Orders::POST_META_KEY, true );

        return get_the_permalink( $page_order ) . '?id=' . urlencode( $order_id_hash ) . '&k=' . urlencode( $order_key );

    }

    /**
     * @param int    $order_id
     * @param string $key
     *
     * @return bool
     */
    public function check_order_key( $order_id, $key ) {
        $order_key = get_post_meta( $order_id, Orders::POST_META_KEY, true );

        return hash_equals( $order_key, $key );
    }

    /**
     * @param string $type
     * @param int    $user_id
     * @param array  $order_data
     *
     * @return bool|int|WP_Error
     */
    public function create_order( $type, $user_id, $order_data = [] ) {

        $user = get_user_by( 'ID', $user_id );

        $order_title = __( $type . ', user ' . $user->user_email );

        // Готовим данные для поста
        $post_data = [
            'post_title'   => $order_title,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_author'  => $user_id,
            'post_type'    => self::POST_TYPE,
        ];

        $order_id = wp_insert_post( $post_data, true );

        // если ошибка -- возвращаем
        if ( is_wp_error( $order_id ) ) {
            return $order_id;
        }

        do_action( 'wpcommunity/order/create_order', $order_id );

        update_post_meta( $order_id, self::POST_META_KEY, $this->generate_order_key() );
        update_post_meta( $order_id, self::POST_META_TYPE, $type );
        update_post_meta( $order_id, self::POST_META_USER_ID, $user_id );

        $result = $this->order_status->change_status( $order_id, OrderStatus::PENDING, $user_id );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $order_id;
    }

    /**
     * @param int              $user_id
     * @param SubscriptionPlan $subscription
     * @param int              $qty
     * @param string           $currency
     *
     * @return int|WP_Error
     */
    public function create_order_subscription( $user_id, SubscriptionPlan $subscription, $qty, $currency ) {
        $order_id = $this->create_order( self::ORDER_TYPE_SUBSCRIPTION, $user_id );

        // если ошибка -- возвращаем ошибку WP_Error
        if ( is_wp_error( $order_id ) ) {
            return $order_id;
        }

        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_ID, $subscription->id );
        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_PRICE, $subscription->price );
        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_QTY, $qty );
        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_DAYS, $subscription->calc_days( $qty ) );

        $price       = $subscription->price * $qty;
        $price_total = $price; // todo потом поменяется, если учитывать какие нибудь баллы или баланс

        update_post_meta( $order_id, self::POST_META_CURRENCY, $currency );
        update_post_meta( $order_id, self::POST_META_PRICE, $price );
        update_post_meta( $order_id, self::POST_META_PRICE_TOTAL, $price_total );

        // отправляем письмо
        $mail = theme_container()->get( Mail::class );
        $mail->create_order_mail( $order_id );

        return $order_id;
    }

    /**
     * @param int $parent_order_id
     *
     * @return int|WP_Error
     */
    public function create_recurring_order( $parent_order_id ) {
        $parent_order = $this->get_order( $parent_order_id );

        $subscription = $parent_order->get_subscription();

        if ( ! $subscription->id ) {
            return new WP_Error( 'wrong_data', __( 'Unable to find subscription plan', 'wpcommunity' ) );
        }

        if ( ! get_setting( "subscription.{$subscription->id}.enabled" ) ) {
            return new WP_Error( 'wrong_data', __( 'Unable to use this subscription plan', 'wpcommunity' ) );
        }

        $order_id = $this->create_order( self::ORDER_TYPE_SUBSCRIPTION, $parent_order->user_id );

        if ( is_wp_error( $order_id ) ) {
            return $order_id;
        }

        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_ID, $subscription->id );
        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_PRICE, $subscription->price );
        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_QTY, $parent_order->qty );
        update_post_meta( $order_id, self::POST_META_SUBSCRIPTION_DAYS, $subscription->calc_days( $parent_order->qty ) );

        $price       = $subscription->price * $parent_order->qty;
        $price_total = $price; // todo потом поменяется, если учитывать какие нибудь баллы или баланс

        update_post_meta( $order_id, self::POST_META_CURRENCY, $parent_order->currency );
        update_post_meta( $order_id, self::POST_META_PRICE, $price );
        update_post_meta( $order_id, self::POST_META_PRICE_TOTAL, $price_total );

        // отправляем письмо
        $mail = theme_container()->get( Mail::class );
        $mail->create_order_mail( $order_id );

        return $order_id;
    }

    /**
     * @return string|null
     */
    public function generate_order_key() {
        return wp_generate_password( 15, false );
    }


    /**
     * @param int                 $order_id
     * @param int|string|\WP_User $user
     *
     * @return WP_Error|bool
     */
    public function finish_order( $order_id, $user ) {
        $membership = theme_container()->get( Membership::class );
        $order      = $this->get_order( $order_id );

        $status_changed = $this->order_status->change_status( $order_id, OrderStatus::COMPLETED, $user );
        if ( is_wp_error( $status_changed ) ) {
            return $status_changed;
        }

        // если подписка -- начисляем
        if ( $order->type == self::ORDER_TYPE_SUBSCRIPTION ) {
            if ( $order->subscription_days ) {
                $membership->renew_membership( $order->user_id, $order->subscription_days );
            } else {
                // try to use deprecated value
                $days = ( $order->subscription_months * $order->qty ) * 31;
                $membership->renew_membership( $order->user_id, $days );
            }
        }

        // отправляем письмо
        $mail = theme_container()->get( Mail::class );
        $mail->finish_order_mail( $order_id );

        if ( get_post_meta( $order_id, static::POST_META_IS_RECURRING, true ) ) {
            theme_container()->get( RecurringPayments::class )->schedule_recurring_date( $order_id );
        }

        return true;
    }

    /**
     * @param int   $order_id
     * @param float $amount
     *
     * @return bool|WP_Error
     */
    public function confirm_refund( $order_id, $amount ) {
        update_post_meta(
            $order_id,
            self::POST_META_REFUND,
            (float) $amount + (float) get_post_meta( $order_id, self::POST_META_REFUND, true )
        );

        $refund_history   = get_post_meta( $order_id, self::POST_META_REFUND_HISTORY, true ) ?: [];
        $refund_history[] = [
            'created_at_gmt' => current_time( 'mysql' ),
            'amount'         => $amount,
        ];

        update_post_meta( $order_id, self::POST_META_REFUND_HISTORY, $refund_history );

        $this->order_status->force_change_status( $order_id, OrderStatus::REFUNDED );

        return true;
    }

    /**
     * Можно ли оплачивать заказ, проверка статуса
     *
     * @param $order_id
     *
     * @return bool
     */
    public function is_order_payable( $order_id ) {
        $status = $this->get_order_status( $order_id );
        if ( in_array( $status, [ OrderStatus::PENDING, OrderStatus::FAILED ] ) ) {
            return true;
        }

        return false;
    }


    public function _register_post_type() {
        $args = [
            "label"               => __( "Orders", 'wpcommunity' ),
            "description"         => "",
            "public"              => true,
            "publicly_queryable"  => false,
            "show_ui"             => true,
            "show_in_rest"        => false,
            "has_archive"         => false,
            "show_in_menu"        => true,
            "show_in_nav_menus"   => false,
            "delete_with_user"    => false,
            "exclude_from_search" => true,
            "capability_type"     => 'post',
            "map_meta_cap"        => true,
            "hierarchical"        => false,
            "rewrite"             => false,
            "query_var"           => false,
            "menu_position"       => 99,
            "menu_icon"           => "dashicons-money-alt",
            "supports"            => [ "title", "custom-fields", "author" ],
            //			"taxonomies"            => [ self::TAXONOMY_GROUP, self::TAXONOMY_PRODUCT ],
        ];

        register_post_type( self::POST_TYPE, $args );
    }

}
